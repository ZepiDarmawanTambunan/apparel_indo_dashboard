<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { ref, computed } from 'vue';
import { DataTable, Column } from 'primevue';
import { toast } from 'vue3-toastify';
import { useConfirm, ConfirmDialog } from 'primevue';

interface Kategori {
  id: number;
  nama: string;
}

interface Pembayaran {
  id_pembayaran: string;
  order_id: string;
  bayar: number;
  kembalian: number;
  kategori: Kategori;
  status: Kategori;
  user_nama?: string | null;
  created_at?: string;
}

const props = defineProps<{
  order_id: string;
  pembayarans: Pembayaran[];
  breadcrumbs: { title: string; href?: string }[];
}>();

const breadcrumbs = props.breadcrumbs;
const pembayarans = props.pembayarans;
const search = ref('');

const filteredPembayarans = computed(() => {
  if (!search.value) return pembayarans;
  return pembayarans.filter(p =>
    p.id_pembayaran.toLowerCase().includes(search.value.toLowerCase()) ||
    p.kategori.nama.toLowerCase().includes(search.value.toLowerCase())
  );
});

const confirm = useConfirm();
const tombolBatal = (id_pembayaran: String) => {
  confirm.require({
    message: 'Yakin ingin membatalkan pembayaran ini?',
    header: 'Konfirmasi',
    acceptLabel: 'Ya',
    rejectLabel: 'Batal',
    acceptClass: 'p-button-danger',
    accept: () => {
        router.put(route('pembayaran.batal', { id_pembayaran }).toString(), {}, {
        preserveScroll: true,
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

const formatCurrency = (value: number) =>
  new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value);

const onRowClick = (event: any) => {
    const pembayaran = event.data;
    router.get(route('pembayaran.show', pembayaran.id_pembayaran));
};
</script>

<template>
  <Head :title="`Pembayaran Order ${order_id}`" />

  <AppLayout>
    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-xl shadow-md p-6">
        <div class="mb-6 space-y-4">
            <Breadcrumbs :breadcrumbs="breadcrumbs" />

            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                <div class="flex gap-3">
                    <Link
                        :href="route('pembayaran.create')"
                        class="px-4 py-2 font-medium bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer"
                    >
                        Bayar
                    </Link>
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
            <div class="text-center text-gray-400 py-6">Belum ada pembayaran.</div>
          </template>

          <Column field="created_at" header="Tgl" sortable />
          <Column field="id_pembayaran" header="ID Pembayaran" sortable />
          <Column field="bayar" header="Nominal">
            <template #body="{ data }">
              {{ formatCurrency(data.bayar) }}
            </template>
          </Column>
          <Column field="kembalian" header="Kembalian">
            <template #body="{ data }">
              {{ formatCurrency(data.kembalian) }}
            </template>
          </Column>
          <Column field="kategori.nama" header="Jenis" sortable />
          <Column field="status.nama" header="Status" sortable />
          <Column field="user_nama" header="Kasir" sortable>
            <template #body="{ data }">
              {{ data.user_nama ?? '-' }}
            </template>
          </Column>
          <Column header="Aksi">
            <template #body="{ data }">
              <button
                v-if="data.status.nama !== 'Batal'"
                @click="tombolBatal(data.id_pembayaran)"
                class="px-3 py-2 text-sm font-medium text-white rounded bg-red-600 hover:bg-red-700 cursor-pointer"
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
