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

interface SablonPress {
  id: string;
  tgl_selesai?: string | null;
  tgl_batal?: string | null;
  order: Order;
  status: Kategori;
  keterangan?: string | null;
}

const props = defineProps<{
  sablon_press: SablonPress[];
  breadcrumbs: { title: string; href?: string }[];
}>();

const breadcrumbs = props.breadcrumbs;
const sablonPress = props.sablon_press;
const search = ref('');

const filteredSablonPress = computed(() => {
  if (!search.value) return sablonPress;
  return sablonPress.filter(item =>
    item.order.id_order.toLowerCase().includes(search.value.toLowerCase()) ||
    item.order.nama_pelanggan.toLowerCase().includes(search.value.toLowerCase())
  );
});

const onRowClick = (event: any) => {
  const sablonPress = event.data;
  router.get(route('sablon-press-kecil.show', sablonPress.id));
};

const confirm = useConfirm();
const batalSablonPress = (id: string) => {
    confirm.require({
        message: 'Yakin ingin membatalkan data sablon press ini?',
        header: 'Konfirmasi',
        acceptLabel: 'Ya',
        rejectLabel: 'Batal',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.put(route('sablon-press-kecil.batal', id), {}, {
                preserveScroll: true,
                preserveState: false,
                onSuccess: () => {},
                onError: () => toast.error('Gagal membatalkan data sablon press'),
            });
        },
    });
};
</script>

<template>
  <Head title="Sablon Press Kecil" />

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
              placeholder="Cari sablon press..."
              class="w-full sm:w-80 rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
        </div>

        <DataTable
          :value="filteredSablonPress"
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
              Tidak ada data sablon press yang tersedia.
            </div>
          </template>

          <Column field="order.id_order" header="ID Order" sortable />
          <Column field="order.nama_pelanggan" header="Nama Pelanggan" sortable />
          <Column field="order.tgl_deadline" header="Deadline" sortable />
          <Column field="status.nama" header="Status" sortable />
          <Column header="Aksi" :style="{ width: '260px' }">
            <template #body="{ data }">
                <button
                  v-if="!['Proses', 'Batal'].includes(data.status.nama)"
                  @click.stop="batalSablonPress(data.id)"
                  class="px-3 py-2 font-medium rounded bg-red-600 text-white hover:bg-red-700 text-sm cursor-pointer"
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
