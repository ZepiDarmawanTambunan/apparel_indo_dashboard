<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { Select, InputNumber, DataTable, Button, Column } from 'primevue'
import { toast } from 'vue3-toastify'
import { computed, ref } from 'vue'

interface Produk {
  id_produk: string;
  nama: string;
  salaries: Salary[];
}

interface Salary {
  id: number;
  produk_id: string;
  divisi: string;
  salary: number;
  user_id: number;
  user_nama: string;
  created_at: string;
  updated_at: string;
}

const props = defineProps<{
  produks: Produk[];
}>();
const search = ref('');
const expandedRows = ref({});

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

const filteredProduks = computed(() => {
  if (!search.value) return props.produks;
  return props.produks.filter(prd =>
    prd.nama.toLowerCase().includes(search.value.toLowerCase())
  );
});

function submit() {
  form.post(route('data.salary.store'), {
    replace: true,
    onSuccess: () => {
        form.reset();
        toast['success']('Kategori berhasil diubah!', {
            autoClose: 3000,
            position: 'top-right',
        });
    },
  })
}

// START HANDLE CURRENCY
const formatCurrency = (value: number) : string => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value)
}
// END HANDLE CURRENCY

</script>

<template>
  <Head title="Tambah Salary" />
  <AppLayout>
    <div class="py-6 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl p-6 shadow hover:shadow-lg transition">
            <h2 class="text-xl font-semibold mb-6">Tambah Salary</h2>
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block mb-1 font-medium">Produk</label>
                    <Select
                    v-model="form.produk_id"
                    :options="props.produks"
                    filter
                    :filterFields="['id_produk', 'nama']"
                    optionLabel="nama"
                    optionValue="id_produk"
                    placeholder="Pilih Produk"
                    :disabled="props.produks.length === 0"
                    class="w-full"
                    >
                        <template #option="slotProps">
                            <div>
                            {{ slotProps.option.id_produk }} - {{ slotProps.option.nama }}
                            </div>
                        </template>
                    </Select>
                    <div v-if="form.errors.produk_id" class="text-red-500 text-sm">{{ form.errors.produk_id }}</div>
                </div>

                <div>
                    <label class="block mb-1 font-medium">Divisi</label>
                    <Select
                    v-model="form.divisi"
                    :options="divisiOptions"
                    filter
                    :filterFields="['label', 'value']"
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
        <div class="bg-white rounded-xl p-6 shadow hover:shadow-lg transition mt-5">
            <div class="space-y-4">
                <h3 class="text-lg font-semibold">Daftar Salary Produk</h3>

                <!-- Search Input -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <label for="search" class="sr-only">Cari Produk</label>
                <input
                    id="search"
                    type="text"
                    v-model="search"
                    placeholder="Cari produk..."
                    class="w-full sm:w-80 rounded-lg border border-gray-300 px-4 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none transition"
                />
                </div>

                <!-- DataTable -->
                <DataTable
                    v-model:expandedRows="expandedRows"
                    :value="filteredProduks"
                    dataKey="id_produk"
                    responsiveLayout="scroll"
                    paginator
                    :rows="5"
                    :rowsPerPageOptions="[5, 10, 20]"
                    class="text-sm"
                >
                <template #empty>
                    <div class="flex items-center justify-center py-10 text-gray-400">
                        Produk masih kosong.
                    </div>
                </template>

                <!-- Main Table Columns -->
                <Column expander style="width: 5rem" />
                <Column field="nama" header="Nama" />

                <!-- Expansion Template -->
                <template #expansion="slotProps">
                    <div class="p-4 bg-gray-50 rounded-lg">
                    <h4 class="font-semibold mb-3 text-gray-700">Salary:</h4>

                    <DataTable
                        v-if="slotProps.data.salaries.length > 0"
                        :value="slotProps.data.salaries"
                        responsiveLayout="scroll"
                        size="small"
                    >
                        <Column field="divisi" header="Divisi" />
                        <Column field="salary" header="Salary">
                            <template #body="{ data }">
                                {{ formatCurrency(data.salary) }}
                            </template>
                        </Column>
                    </DataTable>

                    <div v-else class="text-gray-400 text-sm italic">
                        Tidak ada salary.
                    </div>
                    </div>
                </template>
                </DataTable>
            </div>
        </div>
    </div>
  </AppLayout>
</template>
