<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import {ref, watch} from 'vue';
import {Select, DataTable, Column, useConfirm, ConfirmDialog } from 'primevue';
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

interface DataDesain {
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
}

interface RiwayatDataDesain {
    id: number;
    data_desain_id: number;
    user_id?: number | null;
    user_nama?: string | null;
    tgl_feedback: string;
    keterangan: string | null;
    feedback: string | null;
    file_url?: string | null;
}

interface User {
  id: number;
  nama: string;
}

const props = defineProps<{
    data_desain: DataDesain & {
        riwayat_data_desain: RiwayatDataDesain[];
    },
    users: User[],
}>();

const filteredUser = ref<User[]>([...props.users]);
const fileRiwayatDataDesain = ref<HTMLInputElement | null>(null);
const selectRiwayatDataDesain = ref<RiwayatDataDesain | null>(null);

console.log(props.data_desain.user_nama);

// START HANDLE FORM & SUBMIT
const formDataDesain = useForm<{
  _method: string;
  user_id: number | null;
  riwayat_data_desain_id: string | null;
  keterangan: string | null;
  feedback: string | null;
  file_riwayat_data_desain: File | null;
}>({
  _method: 'PUT',
  user_id: props.data_desain.user_id ?? null,
  riwayat_data_desain_id: null,
  keterangan: null,
  feedback: null,
  file_riwayat_data_desain: null,
});

const submitDataDesain = () => {
    const data = new FormData();
    data.append('_method', 'PUT');
    data.append('user_id', String(formDataDesain.user_id));
    console.log('user_id', formDataDesain.user_id);
    data.append('keterangan', String(formDataDesain.keterangan));
    data.append('feedback', String(formDataDesain.feedback));
    if(formDataDesain.riwayat_data_desain_id){
        data.append('riwayat_data_desain_id', formDataDesain.riwayat_data_desain_id)
    }
    if (formDataDesain.file_riwayat_data_desain) {
        data.append('file_riwayat_data_desain', formDataDesain.file_riwayat_data_desain);
    }
    formDataDesain.post(route('desain-data.update', props.data_desain.id), {
        forceFormData: true,
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            formDataDesain.reset('riwayat_data_desain_id', 'keterangan', 'keterangan', 'feedback', 'file_riwayat_data_desain');
            if (fileRiwayatDataDesain.value) fileRiwayatDataDesain.value.value = '';
            formDataDesain.clearErrors();
        },
        onError: (errors) => {
            console.log(errors);
        }
    });
};
// END HANDLE FORM & SUBMIT

// START HANDLE FILE
function onFileChangeRiwayatDataDesain(event: Event) {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    const file = target.files[0];
    if (file.type !== 'application/pdf') {
      toast.error('File harus berupa PDF.');
      target.value = '';
      return;
    }
    if (file.size > 10 * 1024 * 1024) {
      toast.error('Ukuran file tidak boleh lebih dari 10MB.');
      target.value = '';
      return;
    }
    formDataDesain.file_riwayat_data_desain = file;
  }
}

function getObjectURL(file: File) {
  return URL.createObjectURL(file);
}
// END HANDLE FILE

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
            router.put(route('desain-data.selesai', props.data_desain.id), {}, {
                preserveScroll: true,
                onSuccess: () => {},
                onError: () => toast.error('Gagal menyelesaikan data desain'),
            });
        },
    });
};
// END HANDLE SELESAI

// START HANDLE BATAL
const tombolBatal = () => {
    confirm.require({
        message: 'Yakin ingin membatalkan data desain ini?',
        header: 'Konfirmasi',
        acceptLabel: 'Ya',
        rejectLabel: 'Batal',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.put(route('desain-data.batal', props.data_desain.id), {}, {
                preserveScroll: true,
                onSuccess: () => {},
                onError: () => toast.error('Gagal membatalkan data desain'),
            });
        },
    });
};
// END HANDLE BATAL

// START EDIT DATA DESAIN
watch(selectRiwayatDataDesain, (newVal) => {
    if(newVal){
        formDataDesain.riwayat_data_desain_id = String(newVal.id);
        formDataDesain.keterangan = newVal.keterangan ?? '';
        formDataDesain.feedback = newVal.feedback ?? '';
    }
});
// END EDIT DATA DESAIN
</script>

<template>
    <Head title="Detail Data Desain" />
    <AppLayout>
        <div class="py-6 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl p-6 shadow hover:shadow-lg transition">
                <h2 class="text-2xl font-semibold mb-6">Detail Data Desain</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1 font-medium">Order ID</label>
                        <input :value="data_desain.order?.id_order" readonly class="w-full border rounded px-3 py-2" />
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Nama Pelanggan</label>
                        <input :value="data_desain.order?.nama_pelanggan" readonly class="w-full border rounded px-3 py-2" />
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Nomor WA</label>
                        <input :value="data_desain.order?.nohp_wa" readonly class="w-full border rounded px-3 py-2" />
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Status Desain</label>
                        <input :value="data_desain.status?.nama ?? '-'" readonly class="w-full border rounded px-3 py-2" />
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <Link
                    v-if="data_desain.order"
                    :href="route('order.show', data_desain.order_id)"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded hover:bg-blue-700 transition"
                    >
                        Lihat Detail Order
                    </Link>
                </div>
            </div>
            <div class="bg-white rounded-xl p-6 mt-5 shadow hover:shadow-lg transition">
                <h2 class="text-2xl font-semibold mb-6">Input Data Desain</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                    <label class="block mb-1 font-medium">Petugas</label>
                        <Select
                            v-model="formDataDesain.user_id"
                            :options="filteredUser"
                            filter
                            optionLabel="nama"
                            optionValue="id"
                            placeholder="Pilih"
                            :disabled="filteredUser.length === 0"
                            class="w-full"
                        />
                        <span v-if="formDataDesain.errors.user_id" class="text-red-500 text-sm">{{ formDataDesain.errors.user_id }}</span>
                    </div>
                    <div>
                        <label class="block mb-1 font-medium">Keterangan</label>
                        <textarea v-model="formDataDesain.keterangan" class="w-full border rounded px-3 py-2" rows="3" />
                    </div>
                    <div>
                        <label class="block mb-1 font-medium">Feedback</label>
                        <textarea v-model="formDataDesain.feedback" class="w-full border rounded px-3 py-2" rows="3" />
                    </div>
                    <div>
                        <label class="block mb-1 font-medium">Upload PDF (max: 10MB)</label>
                        <input
                            type="file"
                            accept="application/pdf"
                            @change="onFileChangeRiwayatDataDesain"
                            ref="fileRiwayatDataDesain"
                            class="w-full border rounded px-3 py-2 text-sm file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-gray-100 file:text-gray-700"
                        />
                        <span v-if="formDataDesain.errors.file_riwayat_data_desain" class="text-red-500 text-sm">{{ formDataDesain.errors.file_riwayat_data_desain }}</span>
                    </div>
                    <div v-if="formDataDesain.file_riwayat_data_desain">
                        <p class="font-medium mb-2">Preview File:</p>
                        <iframe :src="getObjectURL(formDataDesain.file_riwayat_data_desain)" class="w-full h-64 border rounded"></iframe>
                    </div>
                </div>
                <div class="mt-6 flex flex-wrap justify-end gap-2">
                    <button
                        v-if="!['Selesai', 'Batal', 'Belum Diterima'].includes(data_desain.status?.nama ?? '')"
                        @click="submitDataDesain"
                        type="button"
                        class="px-4 py-2 font-medium bg-indigo-600 text-white rounded hover:bg-indigo-700 transition cursor-pointer"
                        :disabled="formDataDesain.processing"
                    >
                        Simpan
                    </button>
                    <button
                        v-if="!['Selesai', 'Batal', 'Belum Diterima'].includes(data_desain.status?.nama ?? '')"
                        @click="tombolSelesai"
                        type="button"
                        class="px-4 py-2 font-medium bg-green-600 text-white rounded hover:bg-green-700 transition cursor-pointer"
                        :disabled="formDataDesain.processing"
                    >
                        Selesai
                    </button>
                    <button
                        v-if="['Selesai'].includes(data_desain.status?.nama ?? '')"
                        @click.stop="tombolBatal"
                        class="px-4 py-2 font-medium rounded bg-red-600 text-white hover:bg-red-700 transition cursor-pointer"
                        title="Batal"
                        >
                        Batal
                    </button>
                </div>
            </div>
            <div class="bg-white rounded-xl p-6 mt-5 shadow hover:shadow-lg transition">
                <h2 class="text-2xl font-semibold mb-6">Riwayat Data Desain</h2>
                <DataTable
                    :value="data_desain.riwayat_data_desain"
                    dataKey="id"
                    responsiveLayout="scroll"
                    v-model:selection="selectRiwayatDataDesain"
                    selection-mode="single"
                    >
                    <template #empty>
                        <div class="flex items-center justify-center py-10 text-gray-400">
                            Riwayat data desain masih kosong.
                        </div>
                    </template>
                    <Column field="created_at" header="Tgl" />
                    <Column field="keterangan" header="Keterangan" />
                    <Column field="user_nama" header="Petugas" />
                    <Column field="tgl_feedback" header="Tgl Feedback" />
                    <Column field="feedback" header="Feedback" />
                    <Column field="file_url" header="File">
                        <template #body="slotProps">
                            <a
                                :href="slotProps.data.file_url"
                                target="_blank"
                                rel="noopener"
                                class="px-3 py-2 text-sm font-medium rounded bg-blue-500 text-white hover:bg-blue-600"
                            >
                                Lihat
                            </a>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>
        <ConfirmDialog />
    </AppLayout>
</template>
