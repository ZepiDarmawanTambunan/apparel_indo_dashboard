<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch, computed, toRaw } from 'vue';
import {DataTable, Column, Button, AutoComplete, InputNumber, DatePicker} from 'primevue';
import { v4 as uuidv4 } from 'uuid';
import { toast } from 'vue3-toastify';

interface Satuan {
    id: number;
    nama: string;
}

interface Kategori {
    id: number;
    nama: string;
}

interface Produk {
    id_produk: string;
    nama: string;
    deskripsi?: string | null;
    harga: number;
    stok: number;
    kategori?: Kategori | null;
    satuan?: Satuan | null;
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
}

const props = defineProps<{
  produks: Produk[];
}>();
const showItemInput = ref(false);
const lainnya = ref<number>(0);
const diskon = ref<number>(0);
const tglDeadlineDate = computed<Date | null>({
  get: () => (form.tgl_deadline ? new Date(form.tgl_deadline) : null),
  set: (value) => {
    form.tgl_deadline = value ? value.toISOString().slice(0, 10) : '';
  },
});

// START HANDLE AUTOCOMPLETE
const produkTambahanList  = props.produks.filter((item) => item.kategori?.nama?.toLowerCase() === "tambahan");
const produkUtamaList  = props.produks.filter((item) => item.kategori?.nama?.toLowerCase() !== "tambahan");
const suggestionsProduk = ref<Produk[]>([]);
const suggestionsTambahan = ref<Produk[]>([]);

function searchProduk(event: any) {
  const query = event.query.toLowerCase();
  suggestionsProduk.value = produkUtamaList.filter((item) =>
    item.nama.toLowerCase().includes(query) ||
    item.id_produk.toLowerCase().includes(query)
  );
}

function searchTambahan(event: any) {
  const query = event.query.toLowerCase();
  suggestionsTambahan.value = produkTambahanList.filter((item) =>
    item.nama.toLowerCase().includes(query) ||
    item.id_produk.toLowerCase().includes(query)
  );
}
// END HANDLE AUTOCOMPLETE

// START HANDLE TAMBAH PRODUK & TAMBAH TAMBAHAN
const items = ref<OrderDetail[]>([]);
const tambahanItems = ref<OrderTambahan[]>([]);
const orderId = uuidv4();

const selectedProduk = ref<Produk | null>(null);
const selectedTambahan = ref<Produk | null>(null);
const selectActiveOrderDetail = ref<OrderDetail | null>(null);

function tambahProduk(event: any) {
  const item: Produk = event.value;
  const orderDetailId = uuidv4();
  const existingIndex = items.value.findIndex(p => p.produk_id === item.id_produk);

  if(existingIndex !== -1){
    const existing = items.value[existingIndex];
    existing.qty += 1;
    existing.total = existing.qty * existing.harga;
    items.value.splice(existingIndex, 1, { ...existing });
  }else{
    items.value = [
        ...items.value,
        {
            id: orderDetailId,
            order_id: orderId,
            produk_id: item.id_produk,
            nama: item.nama,
            kategori: item.kategori?.nama ?? '',
            satuan: item.satuan?.nama ?? '',
            qty: 1,
            harga: item.harga,
            total: item.harga,
        }
    ];
    selectedProduk.value = null;
  }
  console.log('Items: ', items);
}

function tambahTambahan(event: any) {
  const item: Produk = event.value;

  if (items.value.length === 0) {
    toast.warn(
        `Tambahkan produk utama terlebih dahulu!`,
        { autoClose: 3000, position: 'top-right' }
    );
    return;
  }

  if (!selectActiveOrderDetail.value) {
    toast.warn(
        `Pilih produk utama (baris) terlebih dahulu!`,
        { autoClose: 3000, position: 'top-right' }
    );
    return;
  }

  const orderTambahanId = uuidv4();
  const activeDetailId = selectActiveOrderDetail.value?.id ?? '';
  const existingIndex = tambahanItems.value.findIndex(
    (p) => p.produk_id === item.id_produk && p.order_detail_id === activeDetailId
  );

  if (existingIndex !== -1) {
    const existing = tambahanItems.value[existingIndex];
    existing.qty += 1;
    existing.total = existing.qty * existing.harga;
    tambahanItems.value.splice(existingIndex, 1, { ...existing });
  }else{
    tambahanItems.value = [
    ...tambahanItems.value,
    {
        id: orderTambahanId,
        order_detail_id: activeDetailId,
        produk_id: item.id_produk,
        nama: item.nama,
        kategori: item.kategori?.nama ?? '',
        satuan: item.satuan?.nama ?? '',
        qty: 1,
        harga: item.harga,
        total: item.harga,
    }
    ];
  }

  selectActiveOrderDetail.value = null;
  selectedTambahan.value = null;
    if(tambahanItems.value){
        toast.success(
            `Item Berhasil Di Tambahkan`,
            { autoClose: 3000, position: 'top-right' }
        );
    }
}

watch(selectActiveOrderDetail, (newVal) => {
    if(newVal){
        // toast.success(
        //     `OrderDetail terpilih: ${newVal?.nama}`,
        //     { autoClose: 3000, position: 'top-right' }
        // );
    }
});
// END HANDLE TAMBAH PRODUK & TAMBAH TAMBAHAN

// START HANDLE EXPANSION
const expandedRows = ref<{ [key: string]: boolean }>({});
function onRowExpand(event: { data: OrderDetail }) {
    expandedRows.value = { ...expandedRows.value, [event.data.id]: true };
}
function onRowCollapse(event: { data: OrderDetail }) {
    const { [event.data.id]: removed, ...rest } = expandedRows.value;
    expandedRows.value = rest;
}
function getTambahanByOrderDetail(orderDetailId: string) {
    return tambahanItems.value.filter(item => item.order_detail_id === orderDetailId);
}
// END HANDLE EXPANSION

// START HANDLE INFO PRICE
const subtotal = computed(() => {
  const totalItems = items.value.reduce((sum, item) => sum + (item.harga * item.qty), 0)
  const totalTambahan = tambahanItems.value.reduce((sum, item) => sum + (item.harga * item.qty), 0)
  return totalItems + totalTambahan
})

const total = computed(() => {
  const afterDiscount = (subtotal.value + lainnya.value) - diskon.value
  return afterDiscount > 0 ? afterDiscount : 0
})

// END HANDLE INFO PRICE

// START HANDLE EDIT QTY
function onCellEditCompleteOrder(event: any) {
    const { data, newValue, field } = event;
    if (field === 'qty') {
        if (!isPositiveInteger(newValue)) {
            toast.warn(
                `Qty harus berupa angka positif.`,
                { autoClose: 3000, position: 'top-right' }
            );
            return;
        }

        const produk = produkUtamaList.find(p => p.id_produk === data.produk_id);
        if (!produk) {
            toast.warn(
                `Produk utama tidak ditemukan.`,
                { autoClose: 3000, position: 'top-right' }
            );
            return;
        }

        // if (newValue > produk.stok) {
        //     toast.warn(
        //         `Stok produk "${produk.nama}" hanya ${produk.stok}.`,
        //         { autoClose: 3000, position: 'top-right' }
        //     );
        //     return;
        // }

        if (newValue === 0) {
            items.value = items.value.filter(item => item.id !== data.id);
            tambahanItems.value = tambahanItems.value.filter(item => item.order_detail_id !== data.id);
        } else {
            data[field] = newValue;
            data.total = data.harga * data.qty;
        }
    }
}
function onCellEditCompleteTambahan(event: any) {
    const { data, newValue, field } = event;
    if (field === 'qty') {
        if (!isPositiveInteger(newValue)) {
            toast.warn(
                `Qty harus berupa angka positif.`,
                { autoClose: 3000, position: 'top-right' }
            );
            return;
        }

        const produk = produkTambahanList.find(p => p.id_produk === data.produk_id);
        if (!produk) {
            toast.warn(
                `Produk tambahan tidak ditemukan.`,
                { autoClose: 3000, position: 'top-right' }
            );
            return;
        }

        // if (newValue > produk.stok) {
        //     toast.warn(
        //         `Stok produk tambahan "${produk.nama}" hanya ${produk.stok}.`,
        //         { autoClose: 3000, position: 'top-right' }
        //     );
        //     return;
        // }

        if (newValue === 0) {
            tambahanItems.value = tambahanItems.value.filter(item => item.id !== data.id);
        } else {
            data[field] = newValue;
            data.total = data.harga * data.qty;
        }
    }
}
function isPositiveInteger(val: any): boolean {
    const str = String(val).trim().replace(/^0+/, '') || '0';
    const n = Math.floor(Number(str));
    return n !== Infinity && String(n) === str && n >= 0;
}
// END HANDLE EDIT QTY

// START HANDLE SUBMIT
const form = useForm({
    id_order: '',
    nama_pelanggan: '',
    nohp_wa: '',
    tgl_deadline: '',
    keterangan: '',
    lainnya: 0,
    diskon: 0,
    sub_total: 0,
    total: 0,
    order_detail: items.value,
    order_tambahan: tambahanItems.value,
});

function submit() {
  form.order_detail = toRaw(items.value);
  form.order_tambahan = toRaw(tambahanItems.value);
  form.lainnya = lainnya.value;
  form.diskon = diskon.value;
  form.sub_total = subtotal.value;
  form.total = total.value;

  form.post(route('order.store'), {
    replace: true,
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
    },
    onError: (errors) => {
      console.error('Error saat submit:', errors);
    },
  });
}
// END HANDLE SUBMIT

// START HANDLE CURRENCY
const formatCurrency = (value: number) : string => {
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
</script>

<template>
    <Head title="Buat Order" />

    <AppLayout>
        <form @submit.prevent="submit">
            <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white rounded-xl p-6 shadow hover:shadow-lg transition">
                    <h2 class="text-2xl font-semibold mb-6">Buat Order</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 space-y-4">
                        <div>
                            <label class="block mb-1 font-medium">Nama</label>
                            <input v-model="form.nama_pelanggan" type="text" class="w-full border rounded px-3 py-2" required />
                            <span v-if="form.errors.nama_pelanggan" class="text-red-500 text-sm">{{ form.errors.nama_pelanggan }}</span>
                        </div>
                        <div>
                            <label class="block mb-1 font-medium">No HP/WA</label>
                            <input v-model="form.nohp_wa" type="text" class="w-full border rounded px-3 py-2" required />
                            <span v-if="form.errors.nohp_wa" class="text-red-500 text-sm">{{ form.errors.nohp_wa }}</span>
                        </div>
                        <div>
                            <label class="block mb-1 font-medium">Tanggal Ambil</label>
                            <DatePicker
                                v-model="tglDeadlineDate"
                                dateFormat="dd-mm-yy"
                                showMonthNavigator
                                showYearNavigator
                                yearRange="2025:2035"
                                showIcon
                                placeholder="Pilih tanggal"
                                class="w-full"
                            />
                            <span v-if="form.errors.tgl_deadline" class="text-red-500 text-sm">{{ form.errors.tgl_deadline }}</span>
                        </div>
                        <div>
                            <label class="block mb-1 font-medium">Keterangan</label>
                            <textarea v-model="form.keterangan" required class="w-full border rounded px-3 py-2" rows="3" />
                            <span v-if="form.errors.keterangan" class="text-red-500 text-sm">{{ form.errors.keterangan }}</span>
                        </div>
                    </div>
                    <div class="pt-4 flex justify-end">
                        <button
                            v-if="!showItemInput"
                            type="button"
                            class="px-4 py-2 text-white font-medium bg-green-600 hover:bg-green-700 mr-2 rounded-md cursor-pointer"
                            :disabled="form.processing"
                            @click="showItemInput = true"
                        >
                            Input Item
                        </button>
                        <button
                            v-if="showItemInput"
                            type="button"
                            class="px-4 py-2 text-white font-medium bg-red-600 hover:bg-red-700 mr-2 rounded-md cursor-pointer"
                            :disabled="form.processing"
                            @click="showItemInput = false"
                        >
                            Tutup
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 text-white font-medium bg-indigo-600 hover:bg-indigo-700 rounded-md cursor-pointer"
                            :disabled="form.processing"
                        >
                            Simpan
                        </button>
                    </div>
                </div>
                <div v-if="showItemInput" class="bg-white rounded-xl p-6 mt-5 shadow hover:shadow-lg transition">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 space-y-4">
                        <div>
                            <label class="block mb-1 font-medium">Cari produk</label>
                            <AutoComplete
                                v-model="selectedProduk"
                                optionLabel="nama"
                                :suggestions="suggestionsProduk"
                                @complete="searchProduk"
                                @item-select="tambahProduk"
                                placeholder="Cari produk utama"
                                emptyMessage="Produk tidak ditemukan"
                                @keydown.enter.prevent
                            >
                                <template #option="slotProps">
                                    <div>
                                    {{ slotProps.option.id_produk }} - {{ slotProps.option.nama }} - {{ formatCurrency(slotProps.option.harga) }}
                                    </div>
                                </template>
                            </AutoComplete>
                        </div>
                        <div>
                            <label class="block mb-1 font-medium">Tambahan</label>
                            <AutoComplete
                                v-model="selectedTambahan"
                                optionLabel="nama"
                                :suggestions="suggestionsTambahan"
                                @complete="searchTambahan"
                                @item-select="tambahTambahan"
                                placeholder="Cari produk tambahan"
                                emptyMessage="Tambahan tidak ditemukan"
                                @keydown.enter.prevent
                            >
                                <template #option="slotProps">
                                    <div>
                                    {{ slotProps.option.id_produk }} - {{ slotProps.option.nama }} - {{ formatCurrency(slotProps.option.harga) }}
                                    </div>
                                </template>
                            </AutoComplete>
                        </div>
                    </div>
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold mb-2">Daftar Order</h3>
                        <DataTable
                            :value="items"
                            dataKey="id"
                            responsiveLayout="scroll"
                            v-model:selection="selectActiveOrderDetail"
                            selection-mode="single"

                            v-model:expandedRows="expandedRows"
                            @rowExpand="onRowExpand"
                            @rowCollapse="onRowCollapse"

                            editMode="cell"
                            @cell-edit-complete="onCellEditCompleteOrder"
                            :pt="{
                                table: { style: 'min-width: 50rem' },
                                column: {
                                    bodycell: ({ state } : any) => ({
                                        class: [{ '!py-0': state['d_editing'] }]
                                    })
                                }
                            }"
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

                            <Column field="qty" header="Qty">
                                <template #editor="{ data, field }">
                                    <InputNumber
                                        v-model="data[field]"
                                        :min="0"
                                        autofocus
                                        @keydown.enter.prevent
                                        />
                                </template>
                            </Column>

                            <Column field="total" header="Total">
                                <template #body="{ data }">
                                    {{ formatCurrency(data.total) }}
                                </template>
                            </Column>

                            <template #expansion="slotProps">
                                <div class="p-3">
                                    <h4 class="font-semibold mb-2">Daftar Tambahan:</h4>
                                    <DataTable
                                        v-if="getTambahanByOrderDetail(slotProps.data.id).length > 0"
                                        :value="getTambahanByOrderDetail(slotProps.data.id)"
                                        responsiveLayout="scroll"
                                        editMode="cell"
                                        @cell-edit-complete="onCellEditCompleteTambahan"
                                        :pt="{
                                            column: {
                                                bodycell: ({ state } : any) => ({
                                                    class: [{ '!py-0': state['d_editing'] }]
                                                })
                                            }
                                        }"
                                    >
                                        <Column field="nama" header="Nama" />
                                        <Column field="harga" header="Harga">
                                            <template #body="{ data }">
                                                {{ formatCurrency(data.harga) }}
                                            </template>
                                        </Column>
                                        <Column field="qty" header="Qty">
                                            <template #editor="{ data, field }">
                                                <InputNumber
                                                    v-model="data[field]"
                                                    :min="0"
                                                    autofocus
                                                    @keydown.enter.prevent
                                                />
                                            </template>
                                        </Column>
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
                            <div class="font-semibold">{{ formatCurrency(subtotal) }}</div>
                        </div>
                        <div class="flex justify-between w-full sm:w-auto gap-4 text-left sm:text-right">
                            <div class="text-gray-500">Lainnya:</div>
                            <input
                                :value="formatCurrency(lainnya)"
                                @input="(e: any) => lainnya = parseCurrency(e.target.value)"
                                @blur="(e: any) => e.target.value = formatCurrency(lainnya)"
                                required
                                inputmode="numeric"
                                class="border rounded px-3 py-1 w-28 text-right"
                                placeholder="0"
                            />
                        </div>
                        <div class="flex justify-between w-full sm:w-auto gap-4 text-left sm:text-right">
                            <div class="text-gray-500">Diskon:</div>
                            <input
                                :value="formatCurrency(diskon)"
                                @input="(e: any) => diskon = parseCurrency(e.target.value)"
                                @blur="(e: any) => e.target.value = formatCurrency(diskon)"
                                required
                                inputmode="numeric"
                                class="border rounded px-3 py-1 w-28 text-right"
                                placeholder="0"
                            />
                        </div>
                        <div class="flex justify-between w-full sm:w-auto gap-4 text-left sm:text-right">
                            <div class="text-gray-500">Total:</div>
                            <div class="font-bold">{{ formatCurrency(total) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </AppLayout>
</template>

<style scoped>
    ::v-deep(.p-autocomplete) {
    width: 100% !important;
    }

    ::v-deep(.p-autocomplete .p-inputtext) {
    width: 100% !important;
    }
</style>
