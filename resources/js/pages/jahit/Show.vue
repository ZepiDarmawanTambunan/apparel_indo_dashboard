<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import {computed, ref, watch} from 'vue';
import {Select, DataTable, Column, useConfirm, ConfirmDialog } from 'primevue';
import { toast } from 'vue3-toastify';
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
    total_qty: number;
    total: number;
    sisa_bayar: number;
    status: Kategori;
    status_pembayaran: Kategori;
    order_detail?: OrderDetail[];
    order_tambahan?: OrderTambahan[];
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

interface Jahit {
    id: number;
    order_id: string;
    status_id: number;
    user_id?: number | null;
    user_nama?: string | null;
    tgl_terima?: string | null;
    tgl_selesai?: string | null;
    tgl_batal?: string | null;
    order?: Order | null;
    status?: Kategori | null;
    keterangan?: string | null;
}

interface RiwayatJahit {
    id: number;
    jahit_id: number;
    user_id: number;
    user_nama: string;
    produk_id: string;
    produk_nama: string;
    salary: number;
    jumlah_dikerjakan: number;
}

interface LaporanKerusakan {
    id: number;
    order_id: string;
    divisi_pelapor: string;
    pelapor_nama: string;
    divisi_bertanggung_jawab: string;
    keterangan: string | null;
    status: Kategori | null;
}

interface User {
    id: number;
    nama: string;
}

interface Produk {
    id_produk: string;
    nama: string;
    qty: number;
    salaries: Salary[];
}

interface Salary {
    id: number;
    produk_id: string;
    divisi: string;
    salary: number;
    user_id: number;
    user_nama: string;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    jahit: Jahit & {
        riwayat_jahit: RiwayatJahit[],
    };
    users: User[];
    produks: Produk[];
    laporan_kerusakan: LaporanKerusakan[];
    breadcrumbs: { title: string; href?: string }[];
}>();

const filteredUser = ref<User[]>([...props.users]);
const filteredProduk = ref<Produk[]>([...props.produks]);
const fotoKerusakan = ref<HTMLInputElement | null>(null);
const selectRiwayatJahit = ref<RiwayatJahit | null>(null);
const selectedProduk = ref<Produk | null>(null);

// START HANDLE FORM & SUBMIT
const formJahit = useForm<{
    user_id: number | null,
    produk_id: string | null,
    riwayat_jahit_id: number | null,
    jumlah_dikerjakan: number | null,
    salary: number,
}>({
    user_id: null,
    produk_id: null,
    riwayat_jahit_id: null,
    jumlah_dikerjakan: 0,
    salary: 0,
});

const submitRiwayatJahit = () => {
    formJahit.put(route('jahit.update', props.jahit.id), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            formJahit.reset();
            selectedProduk.value = null;
            selectRiwayatJahit.value = null;
            formJahit.clearErrors();
        },
        onError: (errors) => console.log(errors),
    });
};
// END HANDLE FORM & SUBMIT

// START HANDLE SELESAI
const confirm = useConfirm();
const tombolSelesai = () => {
    confirm.require({
        message: 'Yakin ingin menandai sebagai selesai?',
        header: 'Konfirmasi',
        acceptLabel: 'Ya',
        rejectLabel: 'Batal',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.put(route('jahit.selesai', props.jahit.id), {}, {
                preserveState: true,
                onSuccess: () => {},
                onError: () => toast.error('Gagal menyelesaikan jahit'),
            });
        },
    });
};
// END HANDLE SELESAI

// START HANDLE BATAL
const tombolBatal = () => {
    confirm.require({
        message: 'Yakin ingin membatalkan jahit ini?',
        header: 'Konfirmasi',
        acceptLabel: 'Ya',
        rejectLabel: 'Batal',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.put(route('jahit.batal', props.jahit.id), {}, {
                preserveState: true,
                onSuccess: () => {},
                onError: () => toast.error('Gagal membatalkan jahit'),
            });
        },
    });
};
// END HANDLE BATAL

// START EDIT DATA DESAIN
watch(selectRiwayatJahit, (newVal) => {
    if(newVal){
        formJahit.riwayat_jahit_id = newVal.id;
        formJahit.user_id = newVal.user_id;
        formJahit.jumlah_dikerjakan = newVal.jumlah_dikerjakan;

        // Cari produk berdasarkan id_produk dari riwayat
        const produk = filteredProduk.value.find(
            (p) => p.id_produk === newVal.produk_id
        ) ?? null;

        selectedProduk.value = produk;
        formJahit.produk_id = produk?.id_produk ?? null;
    }
});
watch(() => formJahit.jumlah_dikerjakan, (val) => {
    const produkQty = selectedProduk.value?.qty ?? 0;
    // Kalau kosong/null, reset ke 0
    if (!val && val !== 0) {
        formJahit.jumlah_dikerjakan = 0;
        return;
    }
    // Skip validasi kalau produkQty 0 (belum pilih produk)
    if (!produkQty) {
        return;
    }
    // Validasi jumlah
    if (val > produkQty) {
        toast.error(`Jumlah dikerjakan tidak boleh melebihi maksimal: ${produkQty}`);
        formJahit.jumlah_dikerjakan = produkQty;
    }
    const jumlah = formJahit.jumlah_dikerjakan ?? 0;
    formJahit.salary = selectSalary.value * jumlah;
});
// END EDIT DATA DESAIN

// START HANDLE FORM LAPORAN KERUSAKAN
const formKerusakan = useForm({
    order_id: props.jahit.order_id,
    keterangan: '',
    divisi_pelapor: 'Jahit',
    jumlah_rusak: 1,
    foto_kerusakan: null as File | null,
});

function submitKerusakan() {
    const data = new FormData();
    data.append('order_id', String(formKerusakan.order_id));
    data.append('keterangan', formKerusakan.keterangan);
    data.append('divisi_pelapor', formKerusakan.divisi_pelapor);
    data.append('jumlah_rusak', String(formKerusakan.jumlah_rusak));
    if (formKerusakan.foto_kerusakan) {
        data.append('foto_kerusakan', formKerusakan.foto_kerusakan);
    }
    formKerusakan.post(route('laporan-kerusakan.store'), {
        forceFormData: true,
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            formKerusakan.reset();
            formKerusakan.foto_kerusakan = null;
            if (fotoKerusakan.value) fotoKerusakan.value.value = '';
            formKerusakan.clearErrors();
        },
        onError: (errors) => console.log(errors),
    });
}
// END HANDLE FORM LAPORAN KERUSAKAN

// START HANDLE TOMBOL SELESAI DAN BATAL LAPORAN KERUSAKAN
function selesaiLaporan(id: number) {
    confirm.require({
        message: 'Yakin ingin menandai laporan sebagai selesai',
        header: 'Konfirmasi',
        acceptLabel: 'Ya',
        rejectLabel: 'Batal',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.put(route('laporan-kerusakan.selesai', id), {}, {
                preserveScroll: true,
                onError: () => {
                    toast.error('Gagal menyelesaikan laporan.')
                }
            })
        },
    });
}
function batalLaporan(id: number) {
    confirm.require({
        message: 'Yakin ingin membatalkan laporan ini?',
        header: 'Konfirmasi',
        acceptLabel: 'Ya',
        rejectLabel: 'Batal',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.put(route('laporan-kerusakan.batal', id), {}, {
                preserveScroll: true,
                onError: () => {
                    toast.error('Gagal membatalkan laporan.')
                }
            })
        },
    });
}
// START HANDLE TOMBOL SELESAI DAN BATAL LAPORAN KERUSAKAN

// START HANDLE FOTO KERUSAKAN
function onFotoChangeKerusakan(event: Event) {
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
    formKerusakan.foto_kerusakan = file;
  }
}
function getObjectURL(file: File) {
  return URL.createObjectURL(file);
}
// END HANDLE FOTO KERUSAKAN

// HANDLE BATAL RIWAYAT CUTTING KAIN
function batalRiwayat(id: number) {
    confirm.require({
        message: 'Yakin ingin membatalkan riwayat ini?',
        header: 'Konfirmasi',
        acceptLabel: 'Ya',
        rejectLabel: 'Batal',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.put(route('cutting-kain.batal-riwayat', id), {}, {
                preserveScroll: true,
                onError: () => {
                    toast.error('Gagal membatalkan riwayat.')
                }
            })
        },
    });
}

// START HANDLE PRODUK CHANGE
function onProdukChange() {
    if (selectedProduk.value) {
        formJahit.produk_id = selectedProduk.value.id_produk
        formJahit.salary = selectSalary.value
    } else {
        formJahit.produk_id = null
        formJahit.salary = 0
    }
}
const selectSalary = computed(() => {
    const produk = selectedProduk.value;
    const jahitSalary = produk?.salaries?.find(
        (sal) => sal.divisi === 'Jahit'
    );
    return jahitSalary?.salary ?? 0;
});
// END HANDLE PRODUK CHANGE

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
</script>

<template>
    <Head title="Detail Jahit" />
    <AppLayout>
        <div class="py-6 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl p-6 shadow hover:shadow-lg transition">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
                    <div>
                        <label class="block mb-1 font-medium">Order ID</label>
                        <input :value="jahit.order?.id_order" readonly class="w-full border rounded px-3 py-2" />
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Nama Pelanggan</label>
                        <input :value="jahit.order?.nama_pelanggan" readonly class="w-full border rounded px-3 py-2" />
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Nomor WA</label>
                        <input :value="jahit.order?.nohp_wa" readonly class="w-full border rounded px-3 py-2" />
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Tanggal Deadline</label>
                        <input :value="jahit.order?.tgl_deadline" readonly class="w-full border rounded px-3 py-2" />
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <Link
                    v-if="jahit.order"
                    :href="route('order.show', jahit.order.id_order)"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 transition cursor-pointer"
                    >
                        Lihat Detail Order
                    </Link>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 mt-5 shadow hover:shadow-lg transition">
                <h2 class="text-2xl font-semibold mb-6">Input Jahit</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1 font-medium">Petugas</label>
                        <Select
                            v-model="formJahit.user_id"
                            :options="filteredUser"
                            filter
                            optionLabel="nama"
                            optionValue="id"
                            placeholder="Pilih Petugas"
                            :disabled="filteredUser.length === 0"
                            class="w-full"
                        />
                        <span v-if="formJahit.errors.user_id" class="text-red-500 text-sm">{{ formJahit.errors.user_id }}</span>
                    </div>
                    <div>
                        <label class="block mb-1 font-medium">Produk</label>
                        <Select
                            v-model="selectedProduk"
                            :options="filteredProduk"
                            filter
                            optionLabel="nama"
                            placeholder="Pilih Produk"
                            class="w-full"
                            @change="onProdukChange"
                        />
                        <span
                            v-if="formJahit.errors.produk_id"
                            class="text-red-500 text-sm"
                        >{{ formJahit.errors.produk_id }}</span>
                    </div>
                    <div>
                        <label class="block mb-1 font-medium">Salary</label>
                        <input
                            :value="formatCurrency(formJahit.salary)"
                            @input="(e: any) => formJahit.salary = parseCurrency(e.target.value)"
                            @blur="(e: any) => e.target.value = formatCurrency(formJahit.salary)"
                            inputmode="numeric"
                            type="text"
                            class="w-full border rounded px-3 py-2"
                        />
                        <span v-if="formJahit.errors.salary" class="text-red-500 text-sm">{{ formJahit.errors.salary }}</span>
                    </div>
                    <div>
                        <label class="block mb-1 font-medium">Jumlah Dikerjakan</label>
                        <input
                            v-model.number="formJahit.jumlah_dikerjakan"
                            type="number"
                            min="0"
                            class="w-full border rounded px-3 py-2"
                        />
                        <span v-if="formJahit.errors.jumlah_dikerjakan" class="text-red-500 text-sm">{{ formJahit.errors.jumlah_dikerjakan }}</span>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button
                        v-if="['Proses'].includes(jahit.status?.nama ?? '')"
                        @click="submitRiwayatJahit"
                        type="button"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition cursor-pointer"
                        :disabled="formJahit.processing"
                    >
                        Simpan
                    </button>
                    <button
                        v-if="['Proses'].includes(jahit.status?.nama ?? '')"
                        @click="tombolSelesai"
                        type="button"
                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition cursor-pointer"
                        :disabled="formJahit.processing"
                    >
                        Selesai
                    </button>
                    <button
                        v-if="['Selesai', 'Proses'].includes(jahit.status?.nama ?? '')"
                        @click.stop="tombolBatal"
                        class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700 transition cursor-pointer"
                        title="Batal"
                        >
                        Batal
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 mt-5 shadow hover:shadow-lg transition">
                <h2 class="text-2xl font-semibold mb-6">Riwayat Jahit</h2>
                <DataTable
                    :value="jahit.riwayat_jahit"
                    dataKey="id"
                    responsiveLayout="scroll"
                    v-model:selection="selectRiwayatJahit"
                    selection-mode="single"
                >
                    <template #empty>
                        <div class="flex items-center justify-center py-10 text-gray-400">
                            Riwayat jahit masih kosong.
                        </div>
                    </template>
                    <Column field="created_at" header="Tgl" />
                    <Column field="user_nama" header="Petugas" />
                    <Column field="produk_nama" header="Produk" />
                    <Column field="salary" header="Upah">
                        <template #body="{ data }">
                            {{ formatCurrency(data.salary) }}
                        </template>
                    </Column>
                    <Column field="jumlah_dikerjakan" header="Jumlah Dikerjakan" />
                    <Column header="Aksi">
                        <template #body="slotProps">
                                <button
                                    v-if="jahit.status?.nama == 'Proses'"
                                    @click="batalRiwayat(slotProps.data.id)"
                                    class="px-3 py-1 text-sm rounded bg-red-600 text-white hover:bg-red-700 cursor-pointer"
                                >
                                    Batal
                                </button>
                        </template>
                    </Column>
                </DataTable>
            </div>

            <div class="bg-white rounded-xl p-6 mt-5 shadow hover:shadow-lg transition">
                <h2 class="text-2xl font-semibold mb-4">Tambah Laporan Kerusakan</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1 font-medium">Keterangan Kerusakan</label>
                        <textarea v-model="formKerusakan.keterangan" class="w-full border rounded px-3 py-2" rows="3" />
                        <span v-if="formKerusakan.errors.keterangan" class="text-red-500 text-sm">
                            {{ formKerusakan.errors.keterangan }}
                        </span>
                    </div>
                    <div>
                        <label class="block mb-1 font-medium">Jumlah Rusak</label>
                        <input v-model="formKerusakan.jumlah_rusak" type="number" class="w-full border rounded px-3 py-2" />
                        <span v-if="formKerusakan.errors.jumlah_rusak" class="text-red-500 text-sm">
                            {{ formKerusakan.errors.jumlah_rusak }}
                        </span>
                    </div>
                    <div>
                        <label class="block mb-1 font-medium">Foto Kerusakan (max: 5MB/foto)</label>
                        <input
                            type="file"
                            accept="image/*"
                            @change="onFotoChangeKerusakan"
                            required
                            multiple
                            class="w-full border rounded px-3 py-2 text-sm file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-gray-100 file:text-gray-700"
                        />
                        <span v-if="formKerusakan.errors.foto_kerusakan" class="text-red-500 text-sm">
                            {{ formKerusakan.errors.foto_kerusakan }}
                        </span>
                    </div>
                    <div v-if="formKerusakan.foto_kerusakan">
                        <p class="font-medium mb-2">Preview Gambar:</p>
                        <img :src="getObjectURL(formKerusakan.foto_kerusakan)" class="w-full max-h-64 object-contain border rounded" />
                    </div>
                </div>
                <div
                    v-if="!['Selesai', 'Batal'].includes(jahit.status?.nama ?? '')"
                    class="mt-6 flex justify-end gap-2">
                    <button @click="submitKerusakan" type="button" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition cursor-pointer" :disabled="formKerusakan.processing">
                        Simpan Laporan
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 mt-5 shadow hover:shadow-lg transition">
                <h2 class="text-2xl font-semibold mb-6">Laporan Kerusakan</h2>
                <DataTable
                    :value="laporan_kerusakan"
                    dataKey="id"
                    responsiveLayout="scroll"
                    >
                    <template #empty>
                        <div class="flex items-center justify-center py-10 text-gray-400">
                            Laporan kerusakan masih kosong.
                        </div>
                    </template>
                    <Column field="created_at" header="Tgl" />
                    <Column field="divisi_pelapor" header="Divisi Pelapor" />
                    <Column field="pelapor_nama" header="Pelapor" />
                    <Column field="keterangan" header="Keterangan" />
                    <Column field="status.nama" header="Status" />
                    <Column header="Aksi">
                        <template #body="slotProps">
                                <div class="flex gap-2">
                                <a
                                    :href="slotProps.data.foto_kerusakan"
                                    target="_blank"
                                    rel="noopener"
                                    class="px-3 py-1 text-sm rounded bg-blue-500 text-white hover:bg-blue-600"
                                >
                                    Lihat
                                </a>
                                <!-- <button
                                    v-if="['Selesai', 'Proses'].includes(slotProps.data.status.nama) && cutting_kain.status?.nama == 'Proses' && slotProps.data.divisi_pelapor == 'Cutting Kain'"
                                    @click="batalLaporan(slotProps.data.id)"
                                    class="px-3 py-1 text-sm rounded bg-red-600 text-white hover:bg-red-700 cursor-pointer"
                                >
                                    Batal
                                </button>
                                <button
                                    v-if="slotProps.data.status.nama == 'Proses' && cutting_kain.status?.nama == 'Proses' && slotProps.data.divisi_pelapor == 'Cutting Kain'"
                                    @click="selesaiLaporan(slotProps.data.id)"
                                    class="px-3 py-1 text-sm rounded bg-green-500 text-white hover:bg-green-600 cursor-pointer"
                                >
                                    Selesai
                                </button> -->
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>
        <ConfirmDialog />
    </AppLayout>
</template>
