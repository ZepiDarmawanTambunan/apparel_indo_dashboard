<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {DataTable, Column, Button} from 'primevue';
import { toast } from 'vue3-toastify';
import Breadcrumbs from '@/components/Breadcrumbs.vue';

interface User {
  id: number;
  pegawai_id?: string | null;
  nama: string;
  password: string;

  created_at?: string;
  updated_at?: string;
  deleted_at?: string | null;
}

const props = defineProps<{
  users: User[];
  breadcrumbs: { title: string; href?: string }[];
}>();

const breadcrumbs = props.breadcrumbs;
const users = props.users;
const search = ref('');

function hapusUser(id: string) {
  if (confirm('Yakin ingin menghapus user ini?')) {
    router.delete(route('data.user.destroy', id), {
      onSuccess: () => {
        router.visit(route('data.user'), { preserveState: false, preserveScroll: true });
        toast['success']('User berhasil dihapus!', {
            autoClose: 3000,
            position: 'top-right',
        });
      },
    });
  }
}

const filteredUsers = computed(() => {
  if (!search.value) return users;
  return users.filter(usr =>
    usr.nama.toLowerCase().includes(search.value.toLowerCase())
  );
});
</script>

<template>
  <Head title="User" />

  <AppLayout>
    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-xl shadow-md p-6">
          <div class="mb-6 space-y-4">
            <Breadcrumbs :breadcrumbs="breadcrumbs" />
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                <div class="flex flex-wrap gap-3">
                    <Link
                        :href="route('data.user.create')"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer"
                    >
                        Buat User
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
                placeholder="Cari user..."
                class="w-full sm:w-80 rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>
        </div>
        <DataTable
        :value="filteredUsers"
        paginator
        :rows="5"
        :rowsPerPageOptions="[5, 10, 20]"
        dataKey="id"
        responsiveLayout="scroll"
        >
        <template #empty>
            <div class="flex items-center justify-center py-10 text-gray-400">
                User masih kosong.
            </div>
        </template>
          <Column field="nama" header="Nama" sortable />
          <Column field="pegawai_id" header="Pegawai Id" />
          <Column header="Aksi" :style="{ width: '260px' }">
            <template #body="slotProps">
                <Link
                    :href="route('data.user.show', slotProps.data.id)"
                    class="mr-2 inline-flex items-center justify-center px-3 py-1 rounded bg-green-300 text-white hover:bg-green-600 text-sm"
                    title="Lihat"
                >
                    👁️
                </Link>
                <Link
                    :href="route('data.user.edit', slotProps.data.id)"
                    class="mr-2 inline-flex items-center justify-center px-3 py-1 rounded bg-yellow-300 text-white hover:bg-yellow-600 text-sm"
                    title="Edit"
                >
                    ✏️
                </Link>
                <button
                    @click="hapusUser(slotProps.data.id)"
                    class="inline-flex items-center justify-center px-3 py-1 rounded bg-red-300 text-white hover:bg-red-600 text-sm cursor-pointer"
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
