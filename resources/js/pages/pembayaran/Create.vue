<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import {ref, watch} from 'vue';
import {Select} from 'primevue';
import { toast } from 'vue3-toastify';

interface Kategori {
    id: number;
    nama: string;
}

interface Order {
    id_order: string;
    nama_pelanggan: string;
    nohp_wa: string;
    tgl_deadline?: string | null;
    keterangan?: string | null;
    diskon: number;
    sub_total: number;
    total: number;
    sisa_bayar: number;
    status: Kategori;
    status_pembayaran: Kategori;
}

interface Pembayaran {
  id_pembayaran: string;
  order_id: string;
  bukti_pembayaran: number;
  kembalian: number;
  kategori_id: number;
}

const props = defineProps<{
  orders: Order[];
  kategori: Kategori[],
}>();

const {orders, kategori} = props;
const filteredKategori = ref<Kategori[]>([...props.kategori]);
const fileInput = ref<HTMLInputElement | null>(null);
const selectedOrderSisaBayar = ref<number>(0);

// START HANDLE SUBMIT
const form = useForm({
    order_id: null,
    kategori_id: null,
    bayar: 0,
    kembalian: 0,
    bukti_pembayaran: null as File | null,
});

function submit() {
    const data = new FormData();
    data.append('order_id', form.order_id ?? '');
    data.append('kategori_id', form.kategori_id ?? '');
    data.append('bayar', String(form.bayar ?? 0));
    data.append('kembalian', String(form.kembalian ?? 0));
    if (form.bukti_pembayaran) {
        data.append('bukti_pembayaran', form.bukti_pembayaran);
    }

    form.post(route('pembayaran.store'), {
        replace: true,
        preserveState: false,
        forceFormData: true,
        onSuccess: () => {
        },
        onError: (errors) => {
            toast.error('Gagal. Masih ada data yang kosong', {
                autoClose: 5000,
                position: 'top-right',
            });
        },
    });
}
// END HANDLE SUBMIT

// START HANDLE KEMBALIAN
function handleKembalian() {
    const bayar = Number(form.bayar) || 0;

    if (selectedOrderSisaBayar.value === 0) {
        form.kembalian = 0;
    } else {
        form.kembalian = Math.max(bayar - selectedOrderSisaBayar.value, 0);
    }
}
// END HANDLE KEMBALIAN

// START HANDLE SELECT ORDER
function handleSelectOrder(orderId: string | null) {
    const selectedOrder = orders.find(order => order.id_order === orderId);

    if (!selectedOrder) {
        resetOrderForm();
        return;
    }

    // ORDER YG MENUNGGU INVOICE TIDAK BISA CREATE PEMBAYARAN
    const status = selectedOrder.status?.nama?.toLowerCase();
    const blockedStatuses = [
        'menunggu invoice dp awal',
        'menunggu invoice dp produksi',
        'menunggu invoice lunas',
    ];
    if (blockedStatuses.includes(status)) {
        toast.warn(
            `Order #${selectedOrder.id_order} atas nama ${selectedOrder.nama_pelanggan} masih menunggu proses invoice (${selectedOrder.status?.nama}). Silakan selesaikan proses invoice terlebih dahulu sebelum melakukan pembayaran.`,
            { autoClose: 5000, position: 'top-right' }
        );
        resetOrderForm();
        return;
    }

    // ORDER YG LUNAS TIDAK BISA CREATE PEMBAYARAN
    const statusPembayaran = selectedOrder.status_pembayaran?.nama?.toLowerCase();
    if (statusPembayaran === 'lunas') {
        toast.warn(
            `Order #${selectedOrder.id_order} atas nama ${selectedOrder.nama_pelanggan} sudah *Lunas*. Tidak perlu melakukan pembayaran lagi.`,
            { autoClose: 5000, position: 'top-right' }
        );
        resetOrderForm();
        return;
    }

    selectedOrderSisaBayar.value = selectedOrder.sisa_bayar;
    handleKembalian(); // HITUNG KEMBALIAN
    filterKategoriByStatusPembayaran(statusPembayaran); // FILTER KATEGORI
    form.kategori_id = null;
    form.bukti_pembayaran = null;
    if (fileInput.value) fileInput.value.value = '';
}

function resetOrderForm() {
    form.order_id = null;
    selectedOrderSisaBayar.value = 0;
    form.kategori_id = null;
    filteredKategori.value = [];
    form.bukti_pembayaran = null;
    if (fileInput.value) fileInput.value.value = '';
    handleKembalian();
}

function filterKategoriByStatusPembayaran(statusPembayaran: string) {
    const lowerStatus = statusPembayaran.toLowerCase();

    // JIKA ORDER BELUM ADA ITEM
    if (selectedOrderSisaBayar.value <= 0) {
        filteredKategori.value = kategori.filter(k =>
            k.nama.toLowerCase() === 'dp awal',
        );
        return;
    }

    if (lowerStatus === 'belum bayar') {
        // JIKA ORDER BELUM BAYAR
        filteredKategori.value = kategori;
    } else if (lowerStatus === 'dp awal') {
        // JIKA ORDER SUDAH DP AWAL
        filteredKategori.value = kategori.filter(k =>
            k.nama.toLowerCase() === 'dp produksi' || k.nama.toLowerCase() === 'lunas'
        );
    } else if (lowerStatus === 'dp produksi') {
        // JIKA ORDER SUDAH DP PRODUKSI
        filteredKategori.value = kategori.filter(k =>
            k.nama.toLowerCase() === 'lunas'
        );
    } else {
        filteredKategori.value = [];
    }
}
// END HANDLE SELECT ORDER

// START HANDLE GAMBAR
function onFileChange(event: Event) {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    const file = target.files[0];
    if (!file.type.startsWith('image/')) {
      toast.error('File harus berupa gambar.');
      target.value = '';
      return;
    }
    if (file.size > 5 * 1024 * 1024) {
      toast.error('Ukuran file tidak boleh lebih dari 5MB.');
      target.value = '';
      return;
    }
    form.bukti_pembayaran = file;
  }
}

function getObjectURL(file: File) {
  return URL.createObjectURL(file);
}
// END HANDLE GAMBAR

// START HANDLE CURRENCY
const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value)
}
const parseCurrency = (value: string): number => {
    return parseInt(value.replace(/[^\d]/g, '') || '0', 10);
};
// END HANDLE CURRENCY

// WATCH
watch(() => form.order_id, (newOrderId) => {
    handleSelectOrder(newOrderId);
});
watch(() => form.bayar, () => {
    handleKembalian();
});
</script>

<template>
    <Head title="Pembayaran" />

    <AppLayout>
        <form @submit.prevent="submit">
            <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white rounded-xl p-6 shadow hover:shadow-lg transition">
                    <h2 class="text-2xl font-semibold mb-6">Pembayaran</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 space-y-4">
                        <div>
                            <label class="block mb-1 font-medium">Cari Order/Pelanggan</label>
                            <Select
                                v-model="form.order_id"
                                :options="props.orders"
                                filter
                                :filterFields="['id_order', 'nama_pelanggan']"
                                optionLabel="id_order"
                                optionValue="id_order"
                                placeholder="Pilih Order"
                                :disabled="props.orders.length === 0"
                                class="w-full"
                                required
                            >
                                <template #option="slotProps">
                                    <div>
                                    {{ slotProps.option.id_order }} - {{ slotProps.option.nama_pelanggan }}
                                    </div>
                                </template>
                            </Select>
                            <span v-if="form.errors.order_id" class="text-red-500 text-sm">{{ form.errors.order_id }}</span>
                        </div>
                        <div>
                            <label class="block mb-1 font-medium">Total/Sisa Bayar</label>
                            <input :value="formatCurrency(selectedOrderSisaBayar)" readonly class="w-full border rounded px-3 py-2" />
                        </div>
                        <div>
                            <label class="block mb-1 font-medium">Bayar</label>
                            <input
                                :value="formatCurrency(form.bayar)"
                                @input="(e: any) => form.bayar = parseCurrency(e.target.value)"
                                @blur="(e: any) => e.target.value = formatCurrency(form.bayar)"
                                required
                                inputmode="numeric"
                                class="w-full border rounded px-3 py-2"
                            />
                            <span v-if="form.errors.bayar" class="text-red-500 text-sm">
                                {{ form.errors.bayar }}
                            </span>
                        </div>
                        <div>
                            <label class="block mb-1 font-medium">Jenis Bayar</label>
                            <Select
                                v-model="form.kategori_id"
                                :options="filteredKategori"
                                filter
                                optionLabel="nama"
                                optionValue="id"
                                placeholder="Pilih jenis bayar"
                                :disabled="filteredKategori.length === 0 || !form.order_id"
                                class="w-full"
                                />
                            <span v-if="form.errors.kategori_id" class="text-red-500 text-sm">{{ form.errors.kategori_id }}</span>
                        </div>
                        <div>
                            <label class="block mb-1 font-medium">Kembalian</label>
                            <input :value="formatCurrency(form.kembalian)" readonly class="w-full border rounded px-3 py-2" />
                        </div>
                        <div>
                            <label for="buktiPembayaran" class="block mb-1 font-medium">Upload Bukti</label>
                            <input
                                id="buktiPembayaran"
                                type="file"
                                accept="image/*"
                                @change="onFileChange"
                                required
                                class="w-full border rounded px-3 py-2 text-sm file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-gray-100 file:text-gray-700"
                            />
                            <span v-if="form.errors.bukti_pembayaran" class="text-red-500 text-sm">{{ form.errors.bukti_pembayaran }}</span>
                        </div>
                        <div v-if="form.bukti_pembayaran" class="mt-4">
                            <p class="font-medium mb-2">Preview Foto:</p>
                            <img :src="getObjectURL(form.bukti_pembayaran)" class="w-32 h-32 object-cover rounded border" />
                        </div>
                    </div>
                    <div class="pt-4 flex justify-end">
                        <button
                            type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 font-medium rounded-md cursor-pointer"
                            :disabled="form.processing"
                        >
                        Simpan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </AppLayout>
</template>

<style scoped>
</style>
