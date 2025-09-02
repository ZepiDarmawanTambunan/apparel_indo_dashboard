<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Select, DataTable, Column, useConfirm, ConfirmDialog } from 'primevue';
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

interface QC {
    id: number;
    order_id: string;
    status_id: number;
    user_id?: number | null;
    user_nama?: string | null;
    tgl_selesai?: string | null;
    tgl_batal?: string | null;
    keterangan?: string | null;
    order?: Order | null;
    status?: Kategori | null;
    file_qc?: string | null;
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

const props = defineProps<{
    qc: QC;
    laporan_kerusakan: LaporanKerusakan[];
    users: User[];
}>();

const filteredUser = ref<User[]>([...props.users]);
const fileQc = ref<HTMLInputElement | null>(null);
const fotoKerusakan = ref<HTMLInputElement | null>(null);

//  START HANDLE QC
const formQc = useForm<{
    _method: string,
    user_id: number | null,
    keterangan: string | null,
    file_qc: File | null,
}>({
  _method: 'PUT',
  user_id: props.qc.user_id ?? null,
  keterangan: props.qc.keterangan ?? '',
  file_qc: null,
});

const submitQc = () => {
    const data = new FormData();
    data.append('_method', 'PUT');
    data.append('user_id', String(formQc.user_id));
    data.append('keterangan', formQc.keterangan ?? '');
    if (formQc.file_qc) {
        data.append('file_qc', formQc.file_qc);
    }
    formQc.post(route('qc.update', props.qc.id), {
        forceFormData: true,
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            formQc.reset('file_qc');
            if (fileQc.value) fileQc.value.value = '';
            formQc.clearErrors();
        },
        onError: (errors) => console.log(errors),
    });
};
// END HANDLE QC

//  START HANDLE FOTO QC
function onFileChangeQc(event: Event) {
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
    formQc.file_qc = file;
  }
}

function getObjectURL(file: File) {
  return URL.createObjectURL(file);
}
// END HANDLE FOTO QC

// START HANDLE TOMBOL SELESAI DAN BATAL
const confirm = useConfirm();
    const tombolSelesai = () => {
        confirm.require({
        message: 'Yakin ingin menandai quality control sebagai selesai?',
        header: 'Konfirmasi',
        acceptLabel: 'Ya',
        rejectLabel: 'Batal',
        acceptClass: 'p-button-danger',
        accept: () => {
                router.put(route('qc.selesai', props.qc.id), {}, {
                preserveScroll: true,
                onError: () => toast.error('Gagal menyelesaikan quality control'),
            });
        },
    });
};

const tombolBatal = () => {
    confirm.require({
        message: 'Yakin ingin membatalkan quality control ini?',
        header: 'Konfirmasi',
        acceptLabel: 'Ya',
        rejectLabel: 'Batal',
        acceptClass: 'p-button-danger',
        accept: () => {
                router.put(route('qc.batal', props.qc.id), {}, {
                preserveScroll: true,
                onError: () => toast.error('Gagal membatalkan quality control'),
            });
        },
    });
};
// START HANDLE TOMBOL SELESAI DAN BATAL

// START HANDLE FORM LAPORAN KERUSAKAN
const formKerusakan = useForm({
  order_id: props.qc.order_id,
  keterangan: '',
  divisi_pelapor: 'QC',
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
// END HANDLE FOTO KERUSAKAN
</script>

<template>
    <Head title="Detail Quality Control" />
    <AppLayout>
        <div class="py-6 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl p-6 shadow hover:shadow-lg transition">
                <h2 class="text-2xl font-semibold mb-6">Detail Quality Control</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1 font-medium">Order ID</label>
                        <input :value="qc.order?.id_order" readonly class="w-full border rounded px-3 py-2" />
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Nama Pelanggan</label>
                        <input :value="qc.order?.nama_pelanggan" readonly class="w-full border rounded px-3 py-2" />
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Nomor WA</label>
                        <input :value="qc.order?.nohp_wa" readonly class="w-full border rounded px-3 py-2" />
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Status Quality Control</label>
                        <input :value="qc.status?.nama ?? '-'" readonly class="w-full border rounded px-3 py-2" />
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <Link
                    v-if="qc.order"
                    :href="route('order.show', qc.order_id)"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 transition cursor-pointer"
                    >
                        Lihat Detail Order
                    </Link>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 mt-5 shadow hover:shadow-lg transition">
                <h2 class="text-2xl font-semibold mb-6">Input Quality Control</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1 font-medium">User</label>
                        <Select
                            v-model="formQc.user_id"
                            :options="filteredUser"
                            filter
                            optionLabel="nama"
                            optionValue="id"
                            placeholder="Pilih User"
                            :disabled="filteredUser.length === 0"
                            class="w-full"
                        />
                        <span v-if="formQc.errors.user_id" class="text-red-500 text-sm">
                            {{ formQc.errors.user_id }}
                        </span>
                    </div>
                    <div>
                        <label class="block mb-1 font-medium">Keterangan</label>
                        <textarea v-model="formQc.keterangan" class="w-full border rounded px-3 py-2" rows="3" />
                    </div>
                    <input
                        type="file"
                        accept="image/*"
                        @change="onFileChangeQc"
                        class="w-full border rounded px-3 py-2 text-sm file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-gray-100 file:text-gray-700"
                    />
                    <div v-if="formQc.file_qc">
                        <p class="font-medium mb-2">Preview Gambar:</p>
                        <img :src="getObjectURL(formQc.file_qc)" class="w-full max-h-64 object-contain border rounded" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <a
                        v-if="qc.file_qc"
                        :href="qc.file_qc"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center px-4 py-2 bg-orange-600 text-white rounded hover:bg-orange-700 transition cursor-pointer"
                    >
                        🔍 Lihat Dokumentasi
                    </a>
                    <button
                        v-if="!['Selesai', 'Batal'].includes(qc.status?.nama ?? '')"
                        @click="submitQc"
                        type="button"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition cursor-pointer"
                        :disabled="formQc.processing"
                    >
                        Simpan
                    </button>
                    <button
                        v-if="!['Selesai', 'Batal'].includes(qc.status?.nama ?? '')"
                        @click="tombolSelesai"
                        type="button"
                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition cursor-pointer"
                        :disabled="formQc.processing"
                    >
                        Selesai
                    </button>
                    <button
                        v-if="['Selesai'].includes(qc.status?.nama ?? '')"
                        @click.stop="tombolBatal"
                        class="px-4 py-2 rounded bg-red-300 text-white hover:bg-red-600 transition cursor-pointer"
                        :disabled="formQc.processing"
                    >
                        Batal
                    </button>
                </div>
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
                    v-if="!['Selesai', 'Batal'].includes(qc.status?.nama ?? '')"
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
                                    v-if="['Selesai', 'Proses'].includes(slotProps.data.status.nama) && qc.status?.nama == 'Proses' && slotProps.data.divisi_pelapor == 'QC'"
                                    @click="batalLaporan(slotProps.data.id)"
                                    class="px-3 py-1 text-sm rounded bg-red-500 text-white hover:bg-red-600 cursor-pointer"
                                >
                                    Batal
                                </button>
                                <button
                                    v-if="slotProps.data.status.nama == 'Proses' && qc.status?.nama == 'Proses' && slotProps.data.divisi_pelapor == 'QC'"
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
