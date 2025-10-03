<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref} from 'vue';
import {DataTable, Column, InputNumber} from 'primevue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';

interface Satuan {
    id: number;
    nama: string;
}

interface Kategori {
    id: number;
    nama: string;
}

interface OrderDetail {
    id: string;
    order_id: string;
    produk_id: string;
    nama: string;
    kategori?: string | null;
    satuan?: string | null;
    qty: number;
    harga: number;
    total: number;
}

interface RiwayatDataDesain {
    id: string;
    file_url: string;
}

interface OrderTambahan {
    id: string;
    order_detail_id: string;
    produk_id: string;
    nama: string;
    kategori?: string | null;
    satuan?: string | null;
    qty: number;
    harga: number;
    total: number;
}

interface Order {
    id_order: string;
    nama_pelanggan: string;
    nohp_wa: string;
    tgl_deadline?: string | null;
    keterangan?: string | null;
    lainnya: number;
    diskon: number;
    sub_total: number;
    total: number;
    order_detail?: OrderDetail[];
    order_tambahan?: OrderTambahan[];
    status: Kategori;
    status_pembayaran: Kategori;
    riwayat_data_desain?: RiwayatDataDesain | null;
}

const props = defineProps<{
    order: Order;
    breadcrumbs: { title: string; href?: string }[];
}>();

const breadcrumbs = props.breadcrumbs;
const showItemInput = ref(false);
const order = props.order;
const expandedRows = ref({});

// START HANDLE CURRENCY
const formatCurrency = (value: number) : string => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value)
}
// END HANDLE CURRENCY
</script>

<template>
    <Head title="Order Detail" />

    <AppLayout>
        <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl p-6 shadow hover:shadow-lg transition">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-center sm:text-left my-5">
                    <div class="border rounded p-2">
                        <div class="text-gray-500 text-sm">Status Order</div>
                        <div class="font-semibold text-lg text-blue-600">
                            {{ order.status?.nama }}
                        </div>
                    </div>
                    <div class="border rounded p-2">
                        <div class="text-gray-500 text-sm">Status Pembayaran</div>
                        <div :class="order.status_pembayaran?.nama === 'Lunas' ? 'text-green-600' : 'text-red-600'" class="font-semibold text-lg">
                            {{ order.status_pembayaran?.nama }}
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 space-y-4">
                    <div>
                        <label class="block mb-1 font-medium">Nama</label>
                        <input :value="order.nama_pelanggan" readonly type="text" class="w-full border rounded px-3 py-2"/>
                    </div>
                    <div>
                        <label class="block mb-1 font-medium">No HP/WA</label>
                        <input :value="order.nohp_wa" readonly type="text" class="w-full border rounded px-3 py-2" />
                    </div>
                    <div>
                        <label class="block mb-1 font-medium">Tanggal Ambil</label>
                        <input :value="order.tgl_deadline?.split('-').reverse().join('-')" readonly type="date" class="w-full border rounded px-3 py-2" />
                    </div>
                    <div>
                        <label class="block mb-1 font-medium">Keterangan</label>
                        <textarea :value="order.keterangan" readonly class="w-full border rounded px-3 py-2" rows="3" />
                    </div>
                </div>
                <div class="pt-4 flex justify-end">
                    <button
                        v-if="!showItemInput"
                        type="button"
                        class="bg-green-600 hover:bg-green-700 mr-2 text-white px-4 py-2 font-medium rounded-md cursor-pointer"
                        @click="showItemInput = true"
                    >
                        Lihat Item
                    </button>
                    <button
                        v-if="showItemInput"
                        type="button"
                        class="bg-red-600 hover:bg-red-700 mr-2 text-white px-4 py-2 font-medium rounded-md cursor-pointer"
                        @click="showItemInput = false"
                    >
                        Tutup
                    </button>
                </div>
            </div>
            <div v-if="showItemInput" class="bg-white rounded-xl p-6 mt-5 shadow hover:shadow-lg transition">
                  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <Link
                    :href="route('order.tracking', order.id_order)"
                    class="inline-block px-4 py-3 rounded-2xl bg-blue-600 text-white text-center font-semibold hover:bg-blue-700 transition"
                    >
                    🔍 Tracking Order
                    </Link>
                    <Link
                    :href="route('order.pembayaran', order.id_order)"
                    class="w-full px-4 py-3 rounded-2xl bg-green-600 text-white text-center font-semibold hover:bg-green-700 transition"
                    >
                    💸 Pembayaran
                    </Link>
                    <Link
                    :href="route('order.kerusakan', order.id_order)"
                    class="w-full px-4 py-3 rounded-2xl bg-red-600 text-white text-center font-semibold hover:bg-red-700 transition"
                    >
                    Lihat Kerusakan
                    </Link>
                    <a
                        v-if="props.order.riwayat_data_desain"
                        :href="props.order.riwayat_data_desain.file_url"
                        target="_blank"
                        rel="noopener"
                        class="w-full px-4 py-3 rounded-2xl bg-orange-600 text-white text-center font-semibold hover:bg-orange-700 transition cursor-pointer"
                    >
                        Lihat Desain
                    </a>
                </div>
            </div>
            <div v-if="showItemInput" class="bg-white rounded-xl p-6 mt-5 shadow hover:shadow-lg transition">
                <div class="mt-6">
                    <h3 class="text-lg font-semibold mb-2">Daftar Order</h3>
                    <DataTable
                        v-model:expandedRows="expandedRows"
                        :value="order.order_detail"
                        dataKey="id"
                        responsiveLayout="scroll"
                        >
                        <template #empty>
                            <div class="flex items-center justify-center py-10 text-gray-400">
                                Order masih kosong.
                            </div>
                        </template>
                        <Column
                            expander
                            style="width: 5rem"
                        />
                        <Column field="nama" header="Nama" />
                        <Column field="harga" header="Harga">
                            <template #body="{ data }">
                                {{ formatCurrency(data.harga) }}
                            </template>
                        </Column>
                        <Column field="qty" header="Qty" />
                        <Column field="total" header="Total">
                            <template #body="{ data }">
                                {{ formatCurrency(data.total) }}
                            </template>
                        </Column>
                        <template #expansion="slotProps">
                            <div class="p-3">
                                <h4 class="font-semibold mb-2">Daftar Tambahan:</h4>
                                <DataTable
                                    v-if="slotProps.data.order_tambahan.length > 0"
                                    :value="slotProps.data.order_tambahan"
                                    responsiveLayout="scroll"
                                >
                                    <Column field="nama" header="Nama" />
                                    <Column field="harga" header="Harga">
                                        <template #body="{ data }">
                                            {{ formatCurrency(data.harga) }}
                                        </template>
                                    </Column>
                                    <Column field="qty" header="Qty" />
                                    <Column field="total" header="Total">
                                        <template #body="{ data }">
                                            {{ formatCurrency(data.total) }}
                                        </template>
                                    </Column>
                                </DataTable>
                                <div
                                    v-else
                                    class="text-gray-400 text-sm italic"
                                >
                                    Tidak ada produk tambahan.
                                </div>
                            </div>
                        </template>
                    </DataTable>
                </div>
            </div>
            <div v-if="showItemInput" class="bg-white rounded-xl p-6 mt-5 shadow hover:shadow-lg transition">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 space-y-4">
                </div>
                <div class="mt-6 flex flex-col sm:items-end space-y-3 w-full">
                    <div class="flex justify-between w-full sm:w-auto gap-4 text-left sm:text-right">
                        <div class="text-gray-500">Subtotal:</div>
                        <div class="font-semibold">{{ formatCurrency(order.sub_total) }}</div>
                    </div>
                    <div class="flex justify-between w-full sm:w-auto gap-4 text-left sm:text-right">
                        <div class="text-gray-500">Lainnya:</div>
                        <input
                            :value="formatCurrency(order.lainnya)"
                            readonly
                            inputmode="numeric"
                            class="border rounded px-3 py-1 w-28 text-right"
                        />
                    </div>
                    <div class="flex justify-between w-full sm:w-auto gap-4 text-left sm:text-right">
                        <div class="text-gray-500">Diskon:</div>
                        <input
                            :value="formatCurrency(order.diskon)"
                            readonly
                            inputmode="numeric"
                            class="border rounded px-3 py-1 w-28 text-right"
                        />
                    </div>
                    <div class="flex justify-between w-full sm:w-auto gap-4 text-left sm:text-right">
                        <div class="text-gray-500">Total:</div>
                        <div class="font-bold">{{ formatCurrency(order.total) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
</style>
