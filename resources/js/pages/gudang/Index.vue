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

interface Order {
  id_order: string;
  nama_pelanggan: string;
  nohp_wa: string;
  tgl_deadline?: string | null;
  keterangan?: string | null;
  diskon: number;
  total: number;
  total_keb_kain?: number | null;
  status_id: number;
  status: Kategori;
  status_pembayaran_id: number;
  status_pembayaran: Kategori;
  user_nama?: string | null;
  user_id?: number | null;
  created_at?: string;
  updated_at?: string;
}

const props = defineProps<{
  orders: Order[];
  breadcrumbs: { title: string; href?: string }[];
}>();

const breadcrumbs = props.breadcrumbs;
const orders = props.orders;
const search = ref('');

// function hapusOrder(id_order: string) {
//   if (confirm('Yakin ingin menghapus order ini?')) {
//     router.delete(route('data.order.destroy', id_order), {
//       onSuccess: () => {
//         router.visit(route('data.order'), { preserveState: false, preserveScroll: true });
//         toast['success']('Order berhasil dihapus!', {
//             autoClose: 3000,
//             position: 'top-right',
//         });
//       },
//     });
//   }
// }

const filteredOrders = computed(() => {
  if (!search.value) return orders;
  return orders.filter(ord =>
    ord.id_order.toLowerCase().includes(search.value.toLowerCase())
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
                    placeholder="Cari order..."
                    class="w-full sm:w-80 rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>
        </div>

        <DataTable
          :value="filteredOrders"
          paginator
          :rows="5"
          :rowsPerPageOptions="[5, 10, 20]"
          dataKey="id"
          responsiveLayout="scroll"
        >
          <Column field="created_at" header="Tgl" sortable />
          <Column field="id_order" header="ID" sortable />
          <Column field="user_nama" header="Nama" sortable />
          <Column field="tgl_deadline" header="Tgl Ambil" sortable />
          <Column field="status.nama" header="Status" sortable />
          <Column header="Aksi" :style="{ width: '260px' }">
            <template #body="slotProps">
                <!-- <Link
                    :href="route('data.order.show', slotProps.data.id_order)"
                    class="mr-2 px-3 py-1 rounded bg-green-300 text-white hover:bg-green-600 text-sm"
                    title="Lihat"
                >
                    👁️
                </Link>
                <Link
                    :href="route('data.order.edit', slotProps.data.id_order)"
                    class="mr-2 px-3 py-1 rounded bg-yellow-300 text-white hover:bg-yellow-600 text-sm"
                    title="Edit"
                >
                    ✏️
                </Link> -->
                <!-- <button
                    @click="hapusOrder(slotProps.data.id_order)"
                    class="px-3 py-1 rounded bg-red-300 text-white hover:bg-red-600 text-sm cursor-pointer"
                    title="Hapus"
                >
                    🗑️
                </button> -->
            </template>
          </Column>
        </DataTable>
      </div>
    </div>
  </AppLayout>
</template>
