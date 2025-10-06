<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import {ref, watch, computed} from 'vue';
import {Select} from 'primevue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';

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
  bayar: number;
  kembalian: number;
  kategori_id: number;
  order?: Order | null;
}

const props = defineProps<{
    pembayaran: Pembayaran;
    kategori: Kategori[];
    breadcrumbs: { title: string; href?: string }[];
}>();

const {pembayaran} = props;
const filteredKategori = ref<Kategori[]>([...props.kategori]);
const fileInput = ref<HTMLInputElement | null>(null);
const sisaBayarSebelumPembayaranIni = computed(() => {
    const order = pembayaran.order;
    const pembayaranIni = (pembayaran.bayar ?? 0) - (pembayaran.kembalian ?? 0);

    if (!order) return 0;

    return (order.sisa_bayar === 0)
        ? order.total // Kalau lunas → tampilkan total order
        : order.sisa_bayar + pembayaranIni; // Kalau belum lunas → kembalikan posisi pembayaran ini
});

// START HANDLE SUBMIT
const form = useForm({
    _method: 'PUT',
    order_id: pembayaran.order_id,
    kategori_id: pembayaran.kategori_id,
    bayar: pembayaran.bayar,
    kembalian: pembayaran.kembalian,
    bukti_pembayaran: null as File | null,
});

function submit() {
    const data = new FormData();
    data.append('_method', 'PUT');
    data.append('order_id', form.order_id ?? '');
    data.append('kategori_id', form.kategori_id.toString() ?? '');
    data.append('bayar', String(form.bayar ?? 0));
    data.append('kembalian', String(form.kembalian ?? 0));
    if (form.bukti_pembayaran) {
        data.append('bukti_pembayaran', form.bukti_pembayaran);
    }

    form.post(route('pembayaran.update', pembayaran.id_pembayaran), {
        replace: true,
        preserveState: false,
        forceFormData: true,
        onSuccess: () => {
        },
        onError: (errors) => {
            console.error('Error saat submit:', errors);
        },
    });
}
// END HANDLE SUBMIT

// START HANDLE KEMBALIAN
function handleKembalian() {
    const bayar = Number(form.bayar) || 0;
    if (sisaBayarSebelumPembayaranIni.value === 0) {
        form.kembalian = 0;
    } else {
        form.kembalian = Math.max(bayar - sisaBayarSebelumPembayaranIni.value, 0);
    }
}
// END HANDLE KEMBALIAN

// START HANDLE GAMBAR
function onFileChange(event: Event) {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    form.bukti_pembayaran = target.files[0];
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
                    <Breadcrumbs :breadcrumbs="breadcrumbs" />
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 space-y-4 mt-6">
                        <div>
                            <label class="block mb-1 font-medium">Order ID</label>
                            <input :value="pembayaran.order?.id_order" readonly class="w-full border rounded px-3 py-2" />
                        </div>
                        <div>
                            <label class="block mb-1 font-medium">Total/Sisa Bayar</label>
                            <input :value="formatCurrency(sisaBayarSebelumPembayaranIni)" readonly class="w-full border rounded px-3 py-2" />
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
                            <span v-if="form.errors.bayar" class="text-red-500 text-sm">{{ form.errors.bayar }}</span>
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
                            <label class="block mb-1 font-medium">Upload Bukti Baru (Opsional)</label>
                            <input
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
                            class="bg-indigo-600 hover:bg-indigo-700 font-medium text-white px-4 py-2 rounded-md cursor-pointer"
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
