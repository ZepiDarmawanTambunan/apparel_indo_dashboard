<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { Select, useConfirm, ConfirmDialog } from 'primevue';
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
    status: Kategori;
    status_pembayaran: Kategori;
}

interface LaporanKerusakan {
    id: number;
    order_id: string;
    divisi_pelapor: string;
    status: Kategori | null;
    status_checking: Kategori | null;
    pelapor_nama: string;
    jumlah_rusak: number;
    divisi_bertanggung_jawab: string;
    keterangan: string | null;
    keterangan_checking: string | null;
    is_human_error: boolean;
    foto_kerusakan: string;
    order: Order;
}

const props = defineProps<{
    divisi_bertanggung_jawab: string[];
    laporan_kerusakan: LaporanKerusakan;
}>();

//  START HANDLE CHECKING
const formChecking = useForm<{
    divisi_bertanggung_jawab: string,
    jumlah_rusak: number,
    keterangan: string | null,
    keterangan_checking: string | null,
    is_human_error: boolean,
}>({
  divisi_bertanggung_jawab: props.laporan_kerusakan.divisi_bertanggung_jawab ?? '',
  jumlah_rusak: props.laporan_kerusakan.jumlah_rusak ?? 0,
  keterangan: props.laporan_kerusakan.keterangan ?? '',
  keterangan_checking: props.laporan_kerusakan.keterangan_checking ?? '',
  is_human_error: props.laporan_kerusakan.is_human_error ?? false,
});

const submitChecking = () => {
    formChecking.put(route('laporan-kerusakan.update', props.laporan_kerusakan.id), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            formChecking.clearErrors();
        },
        onError: (errors) => {
            console.error(errors);
        },
    });
};
// END HANDLE CHECKING

// START HANDLE TOMBOL SELESAI DAN BATAL
const confirm = useConfirm();
function selesaiChecking() {
    confirm.require({
        message: 'Yakin ingin menandai laporan sebagai selesai',
        header: 'Konfirmasi',
        acceptLabel: 'Ya',
        rejectLabel: 'Batal',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.put(route('laporan-kerusakan.selesai.checking', props.laporan_kerusakan.id), {}, {
                preserveScroll: true,
                onError: () => {
                    toast.error('Gagal menyelesaikan laporan.')
                }
            })
        },
    });
}

function batalChecking() {
    confirm.require({
        message: 'Yakin ingin membatalkan laporan ini?',
        header: 'Konfirmasi',
        acceptLabel: 'Ya',
        rejectLabel: 'Batal',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.put(route('laporan-kerusakan.batal.checking', props.laporan_kerusakan.id), {}, {
                preserveScroll: true,
                onError: () => {
                    toast.error('Gagal membatalkan laporan.')
                }
            })
        },
    });
}

function selesaiLaporan(id: number) {
    confirm.require({
        message: 'Yakin menandai selesai laporan ini?',
        header: 'Konfirmasi',
        acceptLabel: 'Ya',
        rejectLabel: 'Batal',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.put(route('laporan-kerusakan.selesai', id), {}, {
                preserveScroll: true,
                onError: () => {
                    toast.error('Gagal memperbarui laporan.')
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
// START HANDLE TOMBOL SELESAI DAN BATAL
</script>


<template>
    <Head title="Detail Checking" />
    <AppLayout>
        <div class="py-6 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl p-6 shadow hover:shadow-lg transition">
                <h2 class="text-2xl font-semibold mb-6">Laporan Kerusakan</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1 font-medium">Order ID</label>
                        <input :value="laporan_kerusakan.order?.id_order" readonly class="w-full border rounded px-3 py-2" />
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Nama Pelanggan</label>
                        <input :value="laporan_kerusakan.order?.nama_pelanggan" readonly class="w-full border rounded px-3 py-2" />
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Status Kerusakan</label>
                        <input :value="laporan_kerusakan.status?.nama ?? '-'" readonly class="w-full border rounded px-3 py-2" />
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Status Checking</label>
                        <input :value="laporan_kerusakan.status_checking?.nama ?? '-'" readonly class="w-full border rounded px-3 py-2" />
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Nama Pelapor</label>
                        <input :value="laporan_kerusakan.pelapor_nama ?? '-'" readonly class="w-full border rounded px-3 py-2" />
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Divisi Pelapor</label>
                        <input :value="laporan_kerusakan.divisi_pelapor ?? '-'" readonly class="w-full border rounded px-3 py-2" />
                    </div>
                </div>
                <div class="mt-6 flex flex-wrap justify-end gap-2">
                    <Link
                        v-if="laporan_kerusakan.order"
                        :href="route('order.show', laporan_kerusakan.order_id)"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 transition cursor-pointer"
                    >
                        Lihat Detail Order
                    </Link>

                    <button
                        v-if="laporan_kerusakan.status && ['Selesai', 'Proses'].includes(laporan_kerusakan.status.nama) && laporan_kerusakan.status_checking?.nama == 'Pending'"
                        @click="batalLaporan(laporan_kerusakan.id)"
                        class="px-4 py-2 text-sm font-medium rounded bg-red-500 text-white hover:bg-red-600 cursor-pointer"
                    >
                        Batal Laporan
                    </button>

                    <!-- <button
                        v-if="laporan_kerusakan.status?.nama == 'Proses' && laporan_kerusakan.status_checking?.nama == 'Pending'"
                        @click="selesaiLaporan(laporan_kerusakan.id)"
                        class="px-4 py-2 text-sm font-medium rounded bg-green-500 text-white hover:bg-green-600 cursor-pointer"
                    >
                        Selesai Laporan
                    </button> -->
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 mt-5 shadow hover:shadow-lg transition">
                <h2 class="text-2xl font-semibold mb-6">Input Checking</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1 font-medium">Divisi Yang Bertanggung Jawab</label>
                        <Select
                            v-model="formChecking.divisi_bertanggung_jawab"
                            :options="props.divisi_bertanggung_jawab"
                            filter
                            placeholder="Pilih Divisi"
                            :disabled="props.divisi_bertanggung_jawab.length === 0"
                            class="w-full"
                        />
                        <span v-if="formChecking.errors.divisi_bertanggung_jawab" class="text-red-500 text-sm">
                            {{ formChecking.errors.divisi_bertanggung_jawab }}
                        </span>
                    </div>
                    <div class="flex items-center mt-2 sm:mt-0">
                        <input
                            id="is_human_error"
                            type="checkbox"
                            v-model="formChecking.is_human_error"
                            class="mr-2"
                        />
                        <label for="is_human_error" class="font-medium">Human Error?</label>
                    </div>
                    <div>
                        <label class="block mb-1 font-medium">Keterangan</label>
                        <textarea v-model="formChecking.keterangan" class="w-full border rounded px-3 py-2" rows="3" />
                    </div>
                    <div>
                        <label class="block mb-1 font-medium">Jumlah Rusak</label>
                        <input
                            type="number"
                            min="1"
                            v-model.number="formChecking.jumlah_rusak"
                            class="w-full border rounded px-3 py-2"
                        />
                        <span v-if="formChecking.errors.jumlah_rusak" class="text-red-500 text-sm">
                            {{ formChecking.errors.jumlah_rusak }}
                        </span>
                    </div>
                    <div>
                        <label class="block mb-1 font-medium">Keterangan Checking</label>
                        <textarea v-model="formChecking.keterangan_checking" class="w-full border rounded px-3 py-2" rows="3" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <a
                        v-if="laporan_kerusakan.foto_kerusakan"
                        :href="laporan_kerusakan.foto_kerusakan"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center px-4 py-2 bg-orange-600 text-white rounded hover:bg-orange-700 transition cursor-pointer"
                    >
                        🔍 Lihat Bukti
                    </a>
                    <button
                        v-if="laporan_kerusakan.status_checking?.nama == 'Pending' && laporan_kerusakan.status?.nama == 'Proses'"
                        @click="submitChecking"
                        type="button"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition cursor-pointer"
                        :disabled="formChecking.processing"
                    >
                        Simpan
                    </button>
                    <button
                        v-if="laporan_kerusakan.status_checking?.nama == 'Pending' && laporan_kerusakan.status?.nama == 'Proses'"
                        @click="selesaiChecking"
                        type="button"
                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition cursor-pointer"
                        :disabled="formChecking.processing"
                    >
                        Selesai
                    </button>
                    <button
                        v-if="laporan_kerusakan.status_checking?.nama == 'Selesai' && laporan_kerusakan.status?.nama == 'Proses'"
                        @click.stop="batalChecking"
                        class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700 transition cursor-pointer"
                        :disabled="formChecking.processing"
                    >
                        Batal
                    </button>
                </div>
            </div>
        </div>
    <ConfirmDialog />
    </AppLayout>
</template>
