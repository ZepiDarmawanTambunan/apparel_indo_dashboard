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

interface Satuan {
  id: number;
  nama: string;
}

interface Produk {
  id_produk: string;
  nama: string;
  deskripsi?: string | null;
  harga: number;
  stok: number;
  kategori_id?: number | null;
  satuan_id?: number | null;
  parent_id?: string | null;

  kategori?: Kategori | null;
  satuan?: Satuan | null;
  parent?: Produk | null;

  created_at?: string;
  updated_at?: string;
  deleted_at?: string | null;
}

const props = defineProps<{
  produks: Produk[];
  breadcrumbs: { title: string; href?: string }[];
}>();

const breadcrumbs = props.breadcrumbs;
const produks = props.produks;
const search = ref('');

function hapusProduk(id_produk: string) {
  if (confirm('Yakin ingin menghapus produk ini?')) {
    router.delete(route('data.produk.destroy', id_produk), {
      onSuccess: () => {
        router.visit(route('data.produk'), { preserveState: false, preserveScroll: true });
        toast['success']('Produk berhasil dihapus!', {
            autoClose: 3000,
            position: 'top-right',
        });
      },
    });
  }
}

const filteredProduks = computed(() => {
  if (!search.value) return produks;
  return produks.filter(prd =>
    prd.nama.toLowerCase().includes(search.value.toLowerCase())
  );
});
</script>

<template>
  <Head title="Produk" />

  <AppLayout>
    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-xl shadow-md p-6">
        <div class="mb-6 space-y-4">
            <Breadcrumbs :breadcrumbs="breadcrumbs" />
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                <div class="flex flex-wrap gap-3">
                    <Link
                        :href="route('data.produk.create')"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer"
                    >
                        Tambah Produk
                    </Link>
                    <button
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 cursor-pointer"
                    >
                        Export Excel
                    </button>
                </div>
                <input
                type="text"
                v-model="search"
                placeholder="Cari produk..."
                class="w-full sm:w-80 rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>
        </div>

        <DataTable
          :value="filteredProduks"
          paginator
          :rows="5"
          :rowsPerPageOptions="[5, 10, 20]"
          dataKey="id"
          responsiveLayout="scroll"
        >
        <template #empty>
            <div class="flex items-center justify-center py-10 text-gray-400">
                Produk masih kosong.
            </div>
        </template>
          <Column field="nama" header="Nama" sortable />
          <Column field="deskripsi" header="Deskripsi" />
          <Column
            field="parent.nama"
            header="Parent"
            sortable
          />
          <Column header="Aksi" :style="{ width: '260px' }">
            <template #body="slotProps">
                <Link
                    :href="route('data.produk.show', slotProps.data.id_produk)"
                    class="mr-2 px-3 py-1 rounded bg-green-300 text-white hover:bg-green-600 text-sm"
                    title="Lihat"
                >
                    👁️
                </Link>
                <Link
                    :href="route('data.produk.edit', slotProps.data.id_produk)"
                    class="mr-2 px-3 py-1 rounded bg-yellow-300 text-white hover:bg-yellow-600 text-sm"
                    title="Edit"
                >
                    ✏️
                </Link>
                <button
                    @click="hapusProduk(slotProps.data.id_produk)"
                    class="px-3 py-1 rounded bg-red-300 text-white hover:bg-red-600 text-sm cursor-pointer"
                    title="Hapus"
                >
                    🗑️
                </button>
            </template>
          </Column>
        </DataTable>
      </div>
    </div>
  </AppLayout>
</template>
