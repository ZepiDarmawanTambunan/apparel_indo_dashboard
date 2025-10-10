<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { defineProps, ref, computed } from 'vue';
import { DataTable, Column, useConfirm, ConfirmDialog } from 'primevue';
import { Head, router } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import { onMounted } from 'vue';

interface Kategori {
  id: number;
  nama: string;
}

interface Order {
  id_order: string;
  nama_pelanggan: string;
  tgl_deadline?: string | null;
  status: Kategori;
  user_nama?: string | null;
}

interface DataDesain {
  id: string;
  created_at: string;
  order: Order;
  status: Kategori;
}

const props = defineProps<{
  data_desain: DataDesain[];
  breadcrumbs: { title: string; href?: string }[];
}>();

const breadcrumbs = props.breadcrumbs;
const dataDesain = props.data_desain;
const search = ref('');

const filteredDataDesain = computed(() => {
  if (!search.value) return dataDesain;
  return dataDesain.filter(dataDesain =>
    dataDesain.id.toLowerCase().includes(search.value.toLowerCase())
  );
});

const onRowClick = (event: any) => {
  const dataDesain = event.data;
  router.get(route('desain-data.show', dataDesain.id));
};

const terimaOrder = (id: string) => {
    router.put(route('desain-data.terima', id), {}, {
        preserveState: false,
        onSuccess: () => {
        }
    });
};

const confirm = useConfirm();
const tombolBatal = (id: string) => {
    confirm.require({
        message: 'Yakin ingin membatalkan data desain ini?',
        header: 'Konfirmasi',
        acceptLabel: 'Ya',
        rejectLabel: 'Batal',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.put(route('desain-data.batal', id), {}, {
                preserveState: false,
                onSuccess: () => {},
                onError: () => toast.error('Gagal membatalkan data desain'),
            });
        },
        reject: () => {
        }
    });
};

onMounted(() => {
  window.addEventListener('popstate', () =>{
    console.log('reload');
    window.location.reload();
  })
})
</script>

<template>
  <Head title="Desain Data" />

  <AppLayout>
    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-xl shadow-md p-6">
        <div class="mb-6 space-y-4">
          <Breadcrumbs :breadcrumbs="breadcrumbs" />
          <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
            <div class="flex gap-3">
            </div>
            <input
              type="text"
              v-model="search"
              placeholder="Cari desain data..."
              class="w-full sm:w-80 rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
        </div>

        <DataTable
          :value="filteredDataDesain"
          paginator
          :rows="5"
          :rowsPerPageOptions="[5, 10, 20]"
          dataKey="id"
          responsiveLayout="scroll"
          @rowClick="onRowClick"
          selection-mode="single"
        >
          <template #empty>
            <div class="flex items-center justify-center py-10 text-gray-400">
              Tidak ada desain data yang tersedia.
            </div>
          </template>
          <Column field="order.id_order" header="ID" sortable />
          <Column field="order.nama_pelanggan" header="Pelanggan" sortable />
          <Column field="order.tgl_deadline" header="Deadline" sortable />
          <Column field="user_nama" header="Petugas" sortable />
          <Column field="status.nama" header="Status" sortable />
          <Column header="Aksi" :style="{ width: '260px' }">
            <template #body="{ data }">
              <div class="flex justify-start">
                <div class="flex flex-wrap gap-2">
                <button
                    v-if="data.status.nama === 'Belum Diterima'"
                    @click.stop="terimaOrder(data.id)"
                    class="inline-flex items-center justify-center px-3 py-2 font-medium rounded bg-green-600 text-white hover:bg-green-700 text-sm cursor-pointer"
                    title="Terima"
                >
                    Terima
                </button>
                <button
                    v-if="['Proses', 'Selesai'].includes(data.status.nama)"
                    @click.stop="tombolBatal(data.id)"
                    class="inline-flex items-center justify-center px-3 py-2 font-medium rounded bg-red-600 text-white hover:bg-red-700 text-sm cursor-pointer"
                    title="Batal"
                >
                    Batal
                </button>
                </div>
              </div>
            </template>
          </Column>
        </DataTable>
      </div>
    </div>
    <ConfirmDialog />
  </AppLayout>
</template>
