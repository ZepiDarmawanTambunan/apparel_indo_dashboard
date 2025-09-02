<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import InputNumber from 'primevue/inputnumber'
import Dropdown from 'primevue/dropdown'
import Button from 'primevue/button'

interface Produk {
  id_produk: string;
  nama: string;
}

const props = defineProps<{
  produks: Produk[];
}>()

const divisiOptions = [
  { label: 'Press Kain', value: 'Press Kain' },
  { label: 'Cutting Kain', value: 'Cutting Kain' },
  { label: 'Jahit', value: 'Jahit' },
  { label: 'Sablon & Press Kecil', value: 'Sablon & Press Kecil' }
]

const form = useForm({
  produk_id: '',
  divisi: '',
  salary: 0,
})

function submit() {
  form.post(route('data.salary.store'), {
    onSuccess: () => {
      form.reset();
    },
  })
}
</script>

<template>
  <Head title="Tambah Salary" />
  <AppLayout>
    <div class="py-6 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-xl p-6 shadow">
        <h2 class="text-xl font-semibold mb-6">Tambah Salary</h2>
        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block mb-1 font-medium">Produk</label>
            <Dropdown
              v-model="form.produk_id"
              :options="props.produks"
              optionLabel="nama"
              optionValue="id_produk"
              placeholder="Pilih Produk"
              class="w-full"
            />
            <div v-if="form.errors.produk_id" class="text-red-500 text-sm">{{ form.errors.produk_id }}</div>
          </div>

          <div>
            <label class="block mb-1 font-medium">Divisi</label>
            <Dropdown
              v-model="form.divisi"
              :options="divisiOptions"
              optionLabel="label"
              optionValue="value"
              placeholder="Pilih Divisi"
              class="w-full"
            />
            <div v-if="form.errors.divisi" class="text-red-500 text-sm">{{ form.errors.divisi }}</div>
          </div>

          <div>
            <label class="block mb-1 font-medium">Salary</label>
            <InputNumber
                v-model="form.salary"
                class="w-full"
                :min="0"
                :allowEmpty="false"
            />
            <div v-if="form.errors.salary" class="text-red-500 text-sm">{{ form.errors.salary }}</div>
          </div>

          <div class="flex justify-end">
            <Button type="submit" label="Simpan" class="p-button-primary" />
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
