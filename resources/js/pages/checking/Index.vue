<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {DataTable, Column, Button} from 'primevue';
import { toast } from 'vue3-toastify';
import Breadcrumbs from '@/components/Breadcrumbs.vue';

interface Kategori {
  id: number;
  nama: string;
}

interface LaporanKerusakan {
  id: number;
  order_id: string;
  created_at: string;
  keterangan: string | null;
  status: Kategori;
  statusChecking: Kategori;
  order: {
    id_order: string;
    nama_pelanggan: string;
    tgl_deadline: string;
  };
}

const props = defineProps<{
  laporanKerusakan: LaporanKerusakan[];
  breadcrumbs: { title: string; href?: string }[];
}>();


const breadcrumbs = props.breadcrumbs;
const laporanKerusakan = props.laporanKerusakan;
const search = ref('');

const onRowClick = (event: any) => {
  const laporanKerusakan = event.data;
  router.get(
    route('checking.show', laporanKerusakan.id)
  );
};

const filteredLaporanKerusakan = computed(() => {
  if (!search.value) return laporanKerusakan;
  return laporanKerusakan.filter(lpk =>
    lpk.order_id.toLowerCase().includes(search.value.toLowerCase()) ||
    lpk.order?.nama_pelanggan?.toLowerCase().includes(search.value.toLowerCase())
  );
});
</script>

<template>
  <Head title="Order" />

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
                    placeholder="Cari ..."
                    class="w-full sm:w-80 rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>
        </div>

        <DataTable
          :value="filteredLaporanKerusakan"
          paginator
          :rows="5"
          :rowsPerPageOptions="[5, 10, 20]"
          dataKey="id"
          responsiveLayout="scroll"
          selectionMode="single"
          @rowClick="onRowClick"
        >
         <template #empty>
            <div class="flex items-center justify-center py-10 text-gray-400">
              Tidak ada data laporan kerusakan yang tersedia.
            </div>
          </template>
          <Column field="created_at" header="Tgl" sortable />
          <Column field="order_id" header="ID Order" sortable />
          <Column field="divisi_pelapor" header="Divisi Pelapor" sortable />
          <Column field="jumlah_rusak" header="Jumlah Rusak" sortable />
          <Column field="status.nama" header="Status" sortable />
          <Column field="status_checking.nama" header="Status Checking" sortable />
        </DataTable>
      </div>
    </div>
  </AppLayout>
</template>
