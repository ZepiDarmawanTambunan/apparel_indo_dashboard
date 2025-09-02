<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {DataTable, Column, useConfirm, ConfirmDialog} from 'primevue';
import { toast } from 'vue3-toastify';
import Breadcrumbs from '@/components/Breadcrumbs.vue';

interface Kategori {
  id: number;
  nama: string;
}

interface Pembayaran {
  id_pembayaran: string;
  order_id: string;
  sisa_bayar: number;
  total: number;
  bayar: number;
  kembalian: number;

  kategori_id: number;
  kategori: Kategori;
  status_id: number;
  status: Kategori;

  user_nama?: string | null;
  created_at?: string;
}

const props = defineProps<{
  pembayarans: Pembayaran[];
  breadcrumbs: { title: string; href?: string }[];
}>();

const breadcrumbs = props.breadcrumbs;
const pembayarans = props.pembayarans;
const search = ref('');

const filteredPembayarans = computed(() => {
  if (!search.value) return pembayarans;
  return pembayarans.filter(pmb =>
    pmb.id_pembayaran.toLowerCase().includes(search.value.toLowerCase()) ||
    pmb.order_id.toLowerCase().includes(search.value.toLowerCase())
  );
});

// START HANDLE CONFIRM DIALOG
const confirm = useConfirm();
const tombolBatal = (id_pembayaran: String) => {
    confirm.require({
        message: 'Yakin ingin membatalkan pembayaran ini?',
        header: 'Konfirmasi',
        acceptLabel: 'Ya',
        rejectLabel: 'Batal',
        acceptClass: 'p-button-danger',
        accept: () => {
                router.put(route('pembayaran.batal', { id_pembayaran: id_pembayaran }).toString(), {}, {
                preserveState: false,
                onSuccess: () => {},
                onError: (errors) => {
                    if (errors.error) {
                        toast.error(errors.error);
                    } else {
                        toast.error('Terjadi kesalahan. Silakan coba lagi.');
                    }
                },
            });
        },
    });
};
//  END HANDLE CONFIRM DIALOG

// START HELPER FUNCTIONS
const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value)
}

const onRowClick = (event: any) => {
    const pembayaran = event.data;
    router.get(route('pembayaran.show', pembayaran.id_pembayaran));
};
//  END HELPER FUNCTIONS
</script>

<template>
  <Head title="Pembayaran" />

  <AppLayout>
    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-xl shadow-md p-6">
        <div class="mb-6 space-y-4">
            <Breadcrumbs :breadcrumbs="breadcrumbs" />
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                <div class="flex flex-wrap gap-3">
                    <Link
                        :href="route('pembayaran.create')"
                        class="px-4 py-2 font-medium bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer"
                    >
                        Bayar
                    </Link>
                    <!-- <button
                        class="px-4 py-2 font-medium bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 cursor-pointer"
                    >
                        Export Excel
                    </button> -->
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
          :value="filteredPembayarans"
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
                Pembayaran masih kosong.
            </div>
        </template>
        <Column field="order_id" header="ID" sortable />
        <Column field="created_at" header="Tgl" sortable />
        <Column field="order.nama_pelanggan" header="Pelanggan" sortable />
        <Column field="bayar" header="Nominal">
            <template #body="{ data }">
                {{ formatCurrency(data.bayar) }}
            </template>
        </Column>
          <Column field="kategori.nama" header="Jenis" sortable />
          <Column field="status.nama" header="Status" sortable />
          <Column header="Aksi" :style="{ width: '260px' }">
            <template #body="slotProps">
                <button
                    v-if="slotProps.data.status.nama !== 'Batal'"
                    @click="tombolBatal(slotProps.data.id_pembayaran)"
                    class="px-3 py-2 font-medium rounded bg-red-600 text-white text-sm hover:bg-red-700 cursor-pointer"
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
