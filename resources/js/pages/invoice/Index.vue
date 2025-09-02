<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {DataTable, Column, useConfirm, ConfirmDialog} from 'primevue';
import { toast } from 'vue3-toastify';
import Breadcrumbs from '@/components/Breadcrumbs.vue';

interface Kategori {
  id: number;
  nama: string;
}

interface Invoice {
  id_invoice: string;
  order_id: string;
  pembayaran_id: string;

  kategori_id: number;
  kategori: Kategori;
  status_id: number;
  status: Kategori;

  user_nama?: string | null;
  user_id?: number | null;
  created_at?: string;
  updated_at?: string;
}

const props = defineProps<{
  invoices: Invoice[];
  breadcrumbs: { title: string; href?: string }[];
}>();

const breadcrumbs = props.breadcrumbs;
const invoices = props.invoices;
const search = ref('');

const filteredInvoices = computed(() => {
  if (!search.value) return invoices;
  return invoices.filter(inv =>
    inv.id_invoice.toLowerCase().includes(search.value.toLowerCase())
  );
});

const onRowClick = (event: any) => {
    const invoice = event.data;
    router.get(route('invoice.show', invoice.id_invoice));
};

// START HANDLE CONFIRM DIALOG
const confirm = useConfirm();
const tombolBatal = (id_invoice: String) => {
    confirm.require({
        message: 'Yakin ingin membatalkan invoice ini?',
        header: 'Konfirmasi',
        acceptLabel: 'Ya',
        rejectLabel: 'Batal',
        acceptClass: 'p-button-danger',
        accept: () => {
                router.post(route('invoice.konfirmasi', {id_invoice: id_invoice}).toString(), {
                    status: 'batal',
                }, {
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
</script>

<template>
  <Head title="Invoice" />

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
                placeholder="Cari invoice..."
                class="w-full sm:w-80 rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>
        </div>

        <DataTable
          :value="filteredInvoices"
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
                Invoice masih kosong.
            </div>
        </template>
          <Column field="id_invoice" header="ID" sortable />
          <Column field="created_at" header="Tgl" sortable />
          <Column field="order.nama_pelanggan" header="Pelanggan" sortable />
          <Column field="kategori.nama" header="Kategori" sortable />
          <Column field="status.nama" header="Status" sortable />
          <Column header="Aksi" :style="{ width: '260px' }">
            <template #body="slotProps">
                <button
                    v-if="slotProps.data.status.nama !== 'Batal'"
                    @click="tombolBatal(slotProps.data.id_invoice)"
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
