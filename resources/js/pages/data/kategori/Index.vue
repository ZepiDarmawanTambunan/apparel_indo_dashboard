<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {DataTable, Column, Button, Select} from 'primevue';
import { toast } from 'vue3-toastify'

interface Kategori {
    id: number;
    nama: string;
    deskripsi?: string | null;
    parent?: Kategori | null;
    parent_id?: number | null;
}

const props = defineProps<{
  kategoris: Kategori[];
}>();

const kategoris = props.kategoris;
const search = ref('');

const form = useForm({
  id: null as number | null,
  nama: '',
  deskripsi: '' as string | null,
  parent_id: null as number | null,
});

const isEdit = ref(false);

function submit() {
  if (isEdit.value && form.id) {
    form.put(route('data.kategori.update', form.id), {
      onSuccess: () => {
        form.reset();
        isEdit.value = false;
        router.visit(route('data.kategori'), { preserveState: false, preserveScroll: true });
        toast['success']('Kategori berhasil diubah!', {
            autoClose: 3000,
            position: 'top-right',
        });
      },
    });
  } else {
    form.post(route('data.kategori.store'), {
      onSuccess: () => {
        form.reset();
        router.visit(route('data.kategori'), { preserveState: false, preserveScroll: true });
        toast['success']('Kategori berhasil dibuat!', {
            autoClose: 3000,
            position: 'top-right',
        });
      },
    });
  }
}

function editKategori(kat: Kategori) {
  form.id = kat.id;
  form.nama = kat.nama;
  form.deskripsi = kat.deskripsi ?? null;
  form.parent_id = kat.parent_id ?? null;
  isEdit.value = true;
}

function batalEdit() {
  form.reset();
  isEdit.value = false;
}

function hapusKategori(id: number) {
  if (confirm('Yakin ingin menghapus kategori ini?')) {
    router.delete(route('data.kategori.destroy', id), {
      onSuccess: () => {
        router.visit(route('data.kategori'), { preserveState: false, preserveScroll: true });
        toast['success']('Kategori berhasil dihapus!', {
            autoClose: 3000,
            position: 'top-right',
        });
      },
    });
  }
}

const filteredKategoris = computed(() => {
  if (!search.value) return kategoris;
  return kategoris.filter(kat =>
    kat.nama.toLowerCase().includes(search.value.toLowerCase())
  );
});

const filteredParentOptions = computed(() =>
  kategoris.filter(k => k.id !== form.id)
);
</script>

<template>
  <Head title="Kategori" />

  <AppLayout>
    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-xl shadow hover:shadow-lg transition p-6 mb-8 hover:bg-gray-50">
        <form @submit.prevent="submit" class="space-y-6">
          <h2 class="text-2xl font-semibold">{{ isEdit ? 'Edit Kategori' : 'Tambah Kategori' }}</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="nama" class="block mb-2 text-sm font-medium text-gray-700">Nama</label>
              <input
                id="nama"
                v-model="form.nama"
                type="text"
                required
                maxlength="255"
                class="w-full rounded-md border border-gray-300 shadow-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Nama kategori"
              />
                <span v-if="form.errors.nama" class="text-red-500 text-sm">{{ form.errors.nama }}</span>
            </div>
            <div>
              <label for="deskripsi" class="block mb-2 text-sm font-medium text-gray-700">Deskripsi</label>
              <textarea
                id="deskripsi"
                v-model="form.deskripsi"
                rows="3"
                class="w-full rounded-md border border-gray-300 shadow-sm px-3 py-2 resize-none focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Deskripsi (opsional)"
              ></textarea>
                <span v-if="form.errors.deskripsi" class="text-red-500 text-sm">{{ form.errors.deskripsi }}</span>
            </div>
          </div>

          <div>
            <label for="parent_id" class="block mb-2 text-sm font-medium text-gray-700">Parent Kategori (opsional)</label>
            <Select
                v-model="form.parent_id"
                :options="filteredParentOptions"
                filter
                optionLabel="nama"
                optionValue="id"
                placeholder="Pilih parent (opsional)"
                :disabled="isEdit && filteredParentOptions.length === 0"
                class="w-full"
            />
            <span v-if="form.errors.parent_id" class="text-red-500 text-sm">{{ form.errors.parent_id }}</span>
          </div>

          <div class="flex items-center space-x-4">
            <button
              type="submit"
              class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer"
            >
              Simpan
            </button>
            <button
              v-if="isEdit"
              type="button"
              @click="batalEdit"
              class="px-6 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-400 cursor-pointer"
            >
              Batal
            </button>
          </div>
        </form>
      </div>

      <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 space-y-4 sm:space-y-0">
          <h2 class="text-2xl font-semibold">Daftar Kategori</h2>
          <input
            type="text"
            v-model="search"
            placeholder="Cari kategori..."
            class="w-full max-w-sm rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
          <button
            @click=""
            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 cursor-pointer"
          >
            Export Excel
          </button>
        </div>

        <DataTable
          :value="filteredKategoris"
          paginator
          :rows="5"
          :rowsPerPageOptions="[5, 10, 20]"
          dataKey="id"
          responsiveLayout="scroll"
        >
            <template #empty>
                <div class="flex items-center justify-center py-10 text-gray-400">
                    Kategori masih kosong.
                </div>
            </template>
          <Column field="nama" header="Nama" sortable />
          <Column field="deskripsi" header="Deskripsi" />
          <Column
            field="parent.nama"
            header="Parent"
            sortable
          />
          <Column header="Aksi" :style="{ width: '180px' }">
            <template #body="slotProps">
                <button
                    @click="editKategori(slotProps.data)"
                    class="mr-2 px-3 py-1 rounded bg-yellow-300 text-white hover:bg-yellow-600 text-sm cursor-pointer"
                    title="Edit"
                >
                    ✏️
                </button>
                <button
                    @click="hapusKategori(slotProps.data.id)"
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
