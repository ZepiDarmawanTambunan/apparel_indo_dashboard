<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import {useConfirm, ConfirmDialog} from 'primevue';

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

interface Invoice {
    id_invoice: string;
    status: Kategori;
    keterangan: string;
}

interface Pembayaran {
    id_pembayaran: string;
    order_id: string;
    bukti_pembayaran: string | null;
    bayar: number;
    kembalian: number;
    kategori: Kategori;
    status: Kategori;
    order: Order;
    invoice: Invoice;
}

const props = defineProps<{
    pembayaran: Pembayaran,
}>();

// START HANDLE CONFIRMATION
const confirm = useConfirm();
const tombolBatal = () => {
    confirm.require({
        message: 'Yakin ingin membatalkan pembayaran ini?',
        header: 'Konfirmasi',
        acceptLabel: 'Ya',
        rejectLabel: 'Batal',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.put(route('pembayaran.batal', { id_pembayaran: props.pembayaran.id_pembayaran }).toString(), {}, {
                preserveState: false,
                onSuccess: () => {},
                onError: (errors) => {
                    if (errors.error) {
                        toast.error(errors.error);
                    } else {
                        toast.error('Terjadi kesalahan. Silakan coba lagi.');
                    }
                },
            });
        },
    });
};
// END HANDLE CONFIRMATION

// START HELPER FUNCTIONS
const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};
// END HELPER FUNCTIONS
</script>

<template>
    <Head title="Detail Pembayaran" />
    <AppLayout>
        <div class="py-8 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl p-6 shadow hover:shadow-lg transition">

                <h2 class="text-3xl font-semibold mb-8 text-gray-800">Detail Pembayaran</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8 text-gray-700">
                    <div>
                        <p class="text-sm text-gray-500">Order ID</p>
                        <p class="font-semibold">{{ pembayaran.order.id_order}}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Nama Pelanggan</p>
                        <p class="font-semibold">{{ pembayaran.order.nama_pelanggan}}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Order</p>
                        <p class="font-semibold">{{ formatCurrency(pembayaran.order.total) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Jumlah Bayar</p>
                        <p class="font-semibold">{{ formatCurrency(pembayaran.bayar) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Kembalian</p>
                        <p class="font-semibold">{{ formatCurrency(pembayaran.kembalian) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Jenis Pembayaran</p>
                        <p class="font-semibold">{{ pembayaran.kategori.nama}}</p>
                    </div>
                </div>

                <div v-if="pembayaran.bukti_pembayaran" class="mt-8 space-y-2">
                    <p class="text-sm text-gray-500">Bukti Pembayaran:</p>
                    <a
                        :href="pembayaran.bukti_pembayaran"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center px-3 py-2 bg-slate-600 text-white text-sm font-medium rounded hover:bg-slate-700 transition"
                    >
                        🔍 Lihat Bukti Bayar
                    </a>
                </div>

                <div v-if="['Tolak', 'Batal'].includes(pembayaran.invoice.status.nama)" class="mt-6 p-4 border border-red-300 bg-red-50 text-red-800 rounded">
                    <p class="font-semibold">Keterangan {{ pembayaran.invoice.status.nama }}:</p>
                    <p>{{ pembayaran.invoice.keterangan || 'Tidak ada keterangan.' }}</p>
                </div>

                <div class="flex justify-end mt-8 gap-3">
                    <a
                        v-if="pembayaran.invoice.status.nama !== 'Batal'"
                        :href="route('invoice.export.pdf', pembayaran.invoice.id_invoice)"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white text-sm font-medium rounded hover:bg-indigo-700 transition"
                    >
                        📄 Invoice
                    </a>
                    <button
                        v-if="pembayaran.status.nama !== 'Batal'"
                        @click="tombolBatal"
                        class="px-3 py-2 rounded bg-red-600 text-white text-sm font-medium hover:bg-red-700 transition cursor-pointer"
                    >
                        ❌ Batal
                    </button>
                </div>
            </div>
        </div>
        <ConfirmDialog />
    </AppLayout>
</template>

<style scoped>
</style>
