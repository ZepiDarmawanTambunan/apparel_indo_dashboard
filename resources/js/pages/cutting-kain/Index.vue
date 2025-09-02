<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { defineProps, ref, computed } from 'vue';
import { DataTable, Column, useConfirm, ConfirmDialog } from 'primevue';
import { Head, router } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';

interface Kategori {    
  id: number;
  nama: string;
}

interface Order {
  id_order: string;
  nama_pelanggan: string;
  tgl_deadline?: string | null;
  status: Kategori;
}

interface CuttingKain {
  id: string;
  tgl_selesai?: string | null;
  tgl_batal?: string | null;
  order: Order;
  status: Kategori;
  keterangan?: string | null;
}

const props = defineProps<{
  cutting_kain: CuttingKain[];
  breadcrumbs: { title: string; href?: string }[];
}>();

const breadcrumbs = props.breadcrumbs;
const cuttingKain = props.cutting_kain;
const search = ref('');

const filteredCuttingKain = computed(() => {
  if (!search.value) return cuttingKain;
  return cuttingKain.filter(item =>
    item.order.id_order.toLowerCase().includes(search.value.toLowerCase()) ||
    item.order.nama_pelanggan.toLowerCase().includes(search.value.toLowerCase())
  );
});

const onRowClick = (event: any) => {
  const cuttingKain = event.data;
  router.get(
    route('cutting-kain.show', cuttingKain.id)
  );
};

const confirm = useConfirm();
const batalCuttingKain = (id: string) => {
    confirm.require({
        message: 'Yakin ingin membatalkan data cutting kain ini?',
        header: 'Konfirmasi',
        acceptLabel: 'Ya',
        rejectLabel: 'Batal',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.put(route('cutting-kain.batal', id), {}, {
                preserveScroll: true,
                preserveState: false,
                onSuccess: () => {},
                onError: () => toast.error('Gagal membatalkan data cutting kain'),
            });
        },
    });
};

const terimaCuttingKain = (id: string) => {
    router.put(route('cutting-kain.terima', id), {}, {
        preserveState: false,
        onSuccess: () => {
        }
    });
};
</script>

<template>
  <Head title="Cutting Kain" />

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
              placeholder="Cari cutting kain..."
              class="w-full sm:w-80 rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
        </div>

        <DataTable
          :value="filteredCuttingKain"
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
              Tidak ada cutting kain yang tersedia.
            </div>
          </template>

          <Column field="order.id_order" header="ID Order" sortable />
          <Column field="order.nama_pelanggan" header="Nama Pelanggan" sortable />
          <Column field="order.tgl_deadline" header="Deadline" sortable />
          <Column field="status.nama" header="Status" sortable />
          <Column header="Aksi" :style="{ width: '260px' }">
            <template #body="{ data }">
                <button
                  v-if="['Belum Diterima'].includes(data.status.nama)"
                  @click.stop="terimaCuttingKain(data.id)"
                  class="mr-2 px-3 py-2 rounded bg-green-300 text-white hover:bg-green-600 text-sm cursor-pointer"
                  title="Terima"
                >
                  Terima
                </button>
                <button
                  v-if="!['Batal', 'Belum Diterima'].includes(data.status.nama)"
                  @click.stop="batalCuttingKain(data.id)"
                  class="px-3 py-2 rounded bg-red-600 text-white hover:bg-red-700 text-sm cursor-pointer"
                  title="Batal"
                >
                  Batal
                </button>
            </template>
          </Column>
        </DataTable>
      </div>
    </div>
    <ConfirmDialog />
  </AppLayout>
</template>
