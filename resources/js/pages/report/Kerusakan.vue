<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import Calendar from 'primevue/calendar'
import Select from 'primevue/select'

interface Kategori {
    id: number;
    nama: string;
}

const props = defineProps<{
    status_kerusakan: Kategori[],
    status_checking: Kategori[],
}>();

const statusKerusakanOptions = computed(() => [
  { id: 0, nama: 'Semua' },
  ...props.status_kerusakan,
]);

const statusCheckingOptions = computed(() => [
  { id: 0, nama: 'Semua' },
  ...props.status_checking,
]);

const selectedDate = ref<Date | null>(null);
const selectedStatusKerusakan = ref<number | null>(0);
const selectedStatusChecking = ref<number | null>(0);

function exportFile(type: 'pdf' | 'excel') {
    if (!selectedDate.value) return

    const date: Date = selectedDate.value
    const month: string = (date.getMonth() + 1).toString().padStart(2, '0')
    const year: string = date.getFullYear().toString()

    const params: Record<string, any> = {
        month,
        year,
    };

    if (selectedStatusKerusakan.value && selectedStatusKerusakan.value !== 0) {
        params.status_kerusakan = selectedStatusKerusakan.value;
    }

    if (selectedStatusChecking.value && selectedStatusChecking.value !== 0) {
        params.status_checking = selectedStatusChecking.value;
    }

    const url: string = route(`report.kerusakan.export.${type}`, params)
    window.open(url, '_blank')
}
</script>

<template>
  <Head title="Laporan Kerusakan" />

  <AppLayout>
    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-xl p-6 shadow hover:shadow-lg transition">
        <h2 class="text-2xl font-semibold mb-6">Laporan Kerusakan</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block mb-1 font-medium">Periode (Bulan & Tahun)</label>
                <Calendar
                v-model="selectedDate"
                view="month"
                dateFormat="mm/yy"
                showIcon
                placeholder="Pilih Bulan dan Tahun"
                class="w-full"
                />
            </div>

            <div>
                <label class="block mb-1 font-medium">Status Kerusakan</label>
                <Select
                v-model="selectedStatusKerusakan"
                :options="statusKerusakanOptions"
                filter
                optionLabel="nama"
                optionValue="id"
                placeholder="Pilih Status Kerusakan"
                :disabled="statusKerusakanOptions.length === 0"
                class="w-full"
                />
            </div>

            <div>
                <label class="block mb-1 font-medium">Status Checking</label>
                <Select
                v-model="selectedStatusChecking"
                :options="statusCheckingOptions"
                filter
                optionLabel="nama"
                optionValue="id"
                placeholder="Pilih Status Checking"
                :disabled="statusCheckingOptions.length === 0"
                class="w-full"
                />
            </div>
        </div>

        <div class="flex flex-wrap gap-2 justify-end mt-4">
          <button
            type="button"
            @click="exportFile('pdf')"
            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md cursor-pointer"
          >
            Export PDF
          </button>
          <button
            type="button"
            @click="exportFile('excel')"
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md cursor-pointer"
          >
            Export Excel
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
