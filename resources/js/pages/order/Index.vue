<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import {DataTable, Column, useConfirm, ConfirmDialog} from 'primevue';
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
  lainnya: number;
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
    can: {
        create: boolean;
        edit: boolean;
        cancel: boolean;
    };
}>();

const breadcrumbs = props.breadcrumbs;
const orders = props.orders;
const search = ref('');

const filteredOrders = computed(() => {
  if (!search.value) return orders;
  return orders.filter(ord =>
    ord.id_order.toLowerCase().includes(search.value.toLowerCase()) ||
    ord.nama_pelanggan.toLowerCase().includes(search.value.toLowerCase())
  );
});

//  START HANDLE CONFIRM DIALOG
const confirm = useConfirm();
function tombolBatal(id_order: string) {
    confirm.require({
        message: 'Yakin ingin membatalkan order ini?',
        header: 'Konfirmasi',
        acceptLabel: 'Ya',
        rejectLabel: 'Batal',
        acceptClass: 'p-button-danger',
        accept: () => {
                router.put(route('order.batal', { id_order: id_order }).toString(), {}, {
                preserveState: false,
                preserveScroll: true,
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
}
//  END HANDLE CONFIRM DIALOG

// START HELPER FUNCTIONS
const onRowClick = (event: any) => {
    const order = event.data;
    router.get(route('order.show', order.id_order));
};
// END HELPER FUNCTIONS

onMounted(() => {
  window.addEventListener('popstate', () =>{
    console.log('reload');
    window.location.reload();
  })
})
</script>

<template>
  <Head title="Order" />

  <AppLayout>
    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-xl shadow-md p-6">
        <div class="mb-6 space-y-4">
            <Breadcrumbs :breadcrumbs="breadcrumbs" />

            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                <div class="flex flex-wrap gap-3">
                    <Link
                        v-if="props.can.create"
                        :href="route('order.create')"
                        class="px-4 py-2 font-medium bg-indigo-600 text-white rounded-md hover:bg-indigo-700 cursor-pointer"
                    >
                        Buat Order
                    </Link>
                    <!-- <button
                        class="px-4 py-2 font-medium bg-green-600 text-white  rounded-md hover:bg-green-700 cursor-pointer"
                    >
                        Export Excel
                    </button> -->
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
          @rowClick="onRowClick"
          selection-mode="single"
        >
        <template #empty>
            <div class="flex items-center justify-center py-10 text-gray-400">
                Order masih kosong.
            </div>
        </template>
          <Column field="id_order" header="ID" sortable />
          <Column field="nama_pelanggan" header="Pelanggan" sortable />
          <Column field="tgl_deadline" header="Tgl Deadline" sortable />
          <Column field="user_nama" header="Petugas" sortable />
          <Column field="status.nama" header="Status" sortable />
          <Column header="Aksi" :style="{ width: '260px' }">
            <template #body="slotProps">
                <Link

                    v-if="slotProps.data.status.nama !== 'Batal' && slotProps.data.status.nama !== 'Selesai' && props.can.edit"
                    :href="route('order.edit', slotProps.data.id_order)"
                    class="inline-flex items-center justify-center mr-2 px-3 py-2 text-sm font-medium rounded bg-orange-600 text-white hover:bg-orange-700"
                >
                    Edit
                </Link>
                <button
                    v-if="slotProps.data.status.nama !== 'Batal' && props.can.cancel"
                    @click="tombolBatal(slotProps.data.id_order)"
                    class="inline-flex items-center justify-center mt-2 md:mt-0 px-3 py-2 text-sm font-medium rounded bg-red-600 text-white hover:bg-red-700 cursor-pointer"
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
