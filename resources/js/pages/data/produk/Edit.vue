<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Select } from 'primevue';

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
}

const props = defineProps<{
  produk: Produk;
  satuans: Satuan[];
  kategoris: Kategori[];
  produks: Produk[];
}>();

const form = useForm<Partial<Produk> & {
  foto_produk: File[];
  is_cover: number | null;
}>({
  ...props.produk,
  foto_produk: [],
  is_cover: null,
});

function onFilesChange(event: Event) {
  const target = event.target as HTMLInputElement;
  if (target.files) {
    form.foto_produk = Array.from(target.files);
    form.is_cover = null;
  }
}

function submit() {
  const data = new FormData();
  data.append('_method', 'PUT');
  data.append('id_produk', form.id_produk ?? '');
  data.append('nama', form.nama ?? '');
  data.append('deskripsi', form.deskripsi ?? '');
  data.append('harga', String(form.harga ?? 0));
  data.append('stok', String(form.stok ?? 0));
  if (form.kategori_id) data.append('kategori_id', String(form.kategori_id));
  if (form.satuan_id) data.append('satuan_id', String(form.satuan_id));
  if (form.parent_id) data.append('parent_id', form.parent_id);
  if (form.is_cover !== null) data.append('is_cover', String(form.is_cover));

  form.foto_produk.forEach((file, index) => {
    data.append(`foto_produk[${index}]`, file);
  });

  router.post(route('data.produk.update', form.id_produk), data, {
    replace: true,
    forceFormData: true,
    onSuccess: () => {
    },
    onError: (errors) => {
      console.error('Error saat submit:', errors);
    },
  });
}

function getObjectURL(file: File) {
  return URL.createObjectURL(file);
}

// START HANDLE CURRENCY
const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value)
}
const parseCurrency = (value: string): number => {
    return parseInt(value.replace(/[^\d]/g, '') || '0', 10);
};
// END HANDLE CURRENCY
</script>

<template>
  <Head title="Edit Produk" />
  <AppLayout>
    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-2xl font-semibold mb-6">Edit Produk</h2>

        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block mb-1 font-medium">ID Produk</label>
            <input v-model="form.id_produk" type="text" class="w-full border rounded px-3 py-2" readonly />
          </div>

          <div>
            <label class="block mb-1 font-medium">Nama</label>
            <input v-model="form.nama" type="text" class="w-full border rounded px-3 py-2" />
            <span v-if="form.errors.nama" class="text-red-500 text-sm">{{ form.errors.nama }}</span>
          </div>

          <div>
            <label class="block mb-1 font-medium">Deskripsi</label>
            <textarea v-model="form.deskripsi" class="w-full border rounded px-3 py-2" rows="3" />
            <span v-if="form.errors.deskripsi" class="text-red-500 text-sm">{{ form.errors.deskripsi }}</span>
          </div>

          <!-- <div class="grid grid-cols-1 sm:grid-cols-2 gap-4"> -->
            <div>
              <label class="block mb-1 font-medium">Harga</label>
              <input
                :value="formatCurrency(form.harga ?? 0)"
                @input="(e: any) => form.harga = parseCurrency(e.target.value)"
                @blur="(e: any) => e.target.value = formatCurrency(form.harga ?? 0)"
                inputmode="numeric"
                class="w-full border rounded px-3 py-2"
              />
              <span v-if="form.errors.harga" class="text-red-500 text-sm">{{ form.errors.harga }}</span>
            </div>

            <!-- <div>
              <label class="block mb-1 font-medium">Stok</label>
              <input v-model="form.stok" type="number" class="w-full border rounded px-3 py-2" />
              <span v-if="form.errors.stok" class="text-red-500 text-sm">{{ form.errors.stok }}</span>
            </div> -->
          <!-- </div> -->

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block mb-1 font-medium">Kategori</label>
              <Select
                v-model="form.kategori_id"
                :options="props.kategoris"
                filter
                optionLabel="nama"
                optionValue="id"
                placeholder="Pilih kategori"
                class="w-full"
              />
              <span v-if="form.errors.kategori_id" class="text-red-500 text-sm">{{ form.errors.kategori_id }}</span>
            </div>

            <div>
              <label class="block mb-1 font-medium">Satuan</label>
              <Select
                v-model="form.satuan_id"
                :options="props.satuans"
                filter
                optionLabel="nama"
                optionValue="id"
                placeholder="Pilih satuan"
                class="w-full"
              />
              <span v-if="form.errors.satuan_id" class="text-red-500 text-sm">{{ form.errors.satuan_id }}</span>
            </div>
          </div>

          <div>
            <label class="block mb-1 font-medium">Parent Produk (jika ada)</label>
            <Select
              v-model="form.parent_id"
              :options="props.produks"
              :disabled="props.produks.length === 0"
              filter
              optionLabel="nama"
              optionValue="id_produk"
              placeholder="Pilih parent"
              class="w-full"
            />
            <span v-if="form.errors.parent_id" class="text-red-500 text-sm">{{ form.errors.parent_id }}</span>
          </div>

          <div>
            <label class="block mb-1 font-medium">Foto Produk (tambahan)</label>
            <input type="file" multiple accept="image/*" @change="onFilesChange" />
            <input
                type="file"
                multiple
                accept="image/*"
                @change="onFilesChange"
                class="w-full border rounded px-3 py-2 text-sm file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-gray-100 file:text-gray-700"
            />
            <span v-if="form.errors.foto_produk" class="text-red-500 text-sm">{{ form.errors.foto_produk }}</span>
          </div>

          <div v-if="form.foto_produk.length > 0" class="mt-4">
            <p class="mb-2 font-semibold">Pilih Cover Foto:</p>
            <div class="grid grid-cols-4 gap-4">
              <div v-for="(file, index) in form.foto_produk" :key="index" class="flex flex-col items-center">
                <img :src="getObjectURL(file)" alt="preview" class="w-20 h-20 object-cover rounded border" />
                <label class="mt-1 flex items-center space-x-1 cursor-pointer">
                  <input
                    type="radio"
                    name="cover_photo"
                    :value="index"
                    v-model="form.is_cover"
                    class="cursor-pointer"
                  />
                  <span>Pilih Cover</span>
                </label>
              </div>
            </div>
          </div>

          <div class="pt-4">
            <button
              type="submit"
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md cursor-pointer"
              :disabled="form.processing"
            >
              Simpan Perubahan
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
