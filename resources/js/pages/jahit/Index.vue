<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { ref, computed, onMounted } from 'vue';
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

interface Jahit {
  id: string;
  tgl_selesai?: string | null;
  tgl_batal?: string | null;
  order: Order;
  status: Kategori;
  keterangan?: string | null;
}

const props = defineProps<{
  jahit: Jahit[];
  breadcrumbs: { title: string; href?: string }[];
}>();

const breadcrumbs = props.breadcrumbs;
const jahit = props.jahit;
const search = ref('');

const filteredJahit = computed(() => {
  if (!search.value) return jahit;
  return jahit.filter(item =>
    item.order.id_order.toLowerCase().includes(search.value.toLowerCase()) ||
    item.order.nama_pelanggan.toLowerCase().includes(search.value.toLowerCase())
  );
});

const onRowClick = (event: any) => {
  const jahit = event.data;
  router.get(
    route('jahit.show', jahit.id)
  );
};

const confirm = useConfirm();
const batalJahit = (id: string) => {
    confirm.require({
        message: 'Yakin ingin membatalkan data jahit ini?',
        header: 'Konfirmasi',
        acceptLabel: 'Ya',
        rejectLabel: 'Batal',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.put(route('jahit.batal', id), {}, {
                preserveScroll: true,
                preserveState: false,
                onSuccess: () => {},
                onError: () => toast.error('Gagal membatalkan data jahit'),
            });
        },
    });
};

const terimaJahit = (id: string) => {
    router.put(route('jahit.terima', id), {}, {
        preserveState: false,
        onSuccess: () => {
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
  <Head title="Jahit" />

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
              placeholder="Cari jahit..."
              class="w-full sm:w-80 rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
        </div>

        <DataTable
          :value="filteredJahit"
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
              Tidak ada data jahit yang tersedia.
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
                    @click.stop="terimaJahit(data.id)"
                    class="inline-flex items-center justify-center px-3 py-2 rounded bg-green-300 text-white hover:bg-green-600 text-sm cursor-pointer"
                    title="Terima"
                >
                    Terima
                </button>
                <button
                    v-if="!['Batal', 'Belum Diterima'].includes(data.status.nama)"
                    @click.stop="batalJahit(data.id)"
                    class="inline-flex items-center justify-center px-3 py-2 rounded bg-red-600 text-white hover:bg-red-700 text-sm cursor-pointer"
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
