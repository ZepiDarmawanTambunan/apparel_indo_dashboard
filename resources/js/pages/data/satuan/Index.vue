<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {DataTable, Column, Button} from 'primevue';
import { toast } from 'vue3-toastify';

interface Satuan {
    id: number;
    nama: string;
}

const props = defineProps<{
  satuans: Satuan[];
}>();

const satuans = props.satuans;
const search = ref('');

const form = useForm({
  id: null as number | null,
  nama: '',
});

const isEdit = ref(false);

function submit() {
  if (isEdit.value && form.id) {
    form.put(route('data.satuan.update', form.id), {
      onSuccess: () => {
        form.reset();
        isEdit.value = false;
        router.visit(route('data.satuan'), { preserveState: false, preserveScroll: true });
        toast['success']('Satuan berhasil diubah!', {
            autoClose: 3000,
            position: 'top-right',
        });
      },
    });
  } else {
    form.post(route('data.satuan.store'), {
      onSuccess: () => {
        form.reset();
        router.visit(route('data.satuan'), { preserveState: false, preserveScroll: true });
        toast['success']('Satuan berhasil dibuat!', {
            autoClose: 3000,
            position: 'top-right',
        });
      },
    });
  }
}

function editSatuan(sat: Satuan) {
  form.id = sat.id;
  form.nama = sat.nama;
  isEdit.value = true;
}

function batalEdit() {
  form.reset();
  isEdit.value = false;
}

function hapusSatuan(id: number) {
  if (confirm('Yakin ingin menghapus satuan ini?')) {
    router.delete(route('data.satuan.destroy', id), {
      onSuccess: () => {
        router.visit(route('data.satuan'), { preserveState: false, preserveScroll: true });
        toast['success']('Satuan berhasil dihapus!', {
            autoClose: 3000,
            position: 'top-right',
        });
      },
    });
  }
}

const filteredSatuans = computed(() => {
  if (!search.value) return satuans;
  return satuans.filter(sat =>
    sat.nama.toLowerCase().includes(search.value.toLowerCase())
  );
});
</script>

<template>
  <Head title="Satuan" />

  <AppLayout>
    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-xl shadow hover:shadow-lg transition p-6 mb-8 hover:bg-gray-50">
        <form @submit.prevent="submit" class="space-y-6">
          <h2 class="text-2xl font-semibold">{{ isEdit ? 'Edit Satuan' : 'Tambah Satuan' }}</h2>
          <div class="grid grid-cols-1 gap-6">
            <div>
              <label for="nama" class="block mb-2 text-sm font-medium text-gray-700">Nama</label>
              <input
                id="nama"
                v-model="form.nama"
                type="text"
                required
                maxlength="255"
                class="w-full rounded-md border border-gray-300 shadow-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Nama satuan"
              />
                <span v-if="form.errors.nama" class="text-red-500 text-sm">{{ form.errors.nama }}</span>
            </div>
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
          <h2 class="text-2xl font-semibold">Daftar Satuan</h2>
          <input
            type="text"
            v-model="search"
            placeholder="Cari satuan..."
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
          :value="filteredSatuans"
          paginator
          :rows="5"
          :rowsPerPageOptions="[5, 10, 20]"
          dataKey="id"
          responsiveLayout="scroll"
        >
            <template #empty>
                <div class="flex items-center justify-center py-10 text-gray-400">
                    Satuan masih kosong.
                </div>
            </template>
          <Column field="nama" header="Nama" sortable />
          <Column header="Aksi" :style="{ width: '180px' }">
            <template #body="slotProps">
                <button
                    @click="editSatuan(slotProps.data)"
                    class="mr-2 px-3 py-1 rounded bg-yellow-300 text-white hover:bg-yellow-600 text-sm cursor-pointer"
                    title="Edit"
                >
                    ✏️
                </button>
                <button
                    @click="hapusSatuan(slotProps.data.id)"
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
