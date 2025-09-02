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
    status_order: Kategori[],
    status_pembayaran: Kategori[],
}>();

const statusOrderOptions = computed(() => [
  { id: 0, nama: 'Semua' },
  ...props.status_order,
]);

const statusPembayaranOptions = computed(() => [
  { id: 0, nama: 'Semua' },
  ...props.status_pembayaran,
]);

const selectedDate = ref<Date | null>(null)
const selectedStatus = ref<number | null>(0);
const selectedStatusPembayaran = ref<number | null>(0);

function exportFile(type: 'pdf' | 'excel') {
    if (!selectedDate.value) return

    const date: Date = selectedDate.value
    const month: string = (date.getMonth() + 1).toString().padStart(2, '0')
    const year: string = date.getFullYear().toString()

    const params: Record<string, any> = {
        month,
        year,
    };

    if (selectedStatus.value && selectedStatus.value !== 0) {
        params.status_order = selectedStatus.value;
    }

    if (selectedStatusPembayaran.value && selectedStatusPembayaran.value !== 0) {
        params.status_pembayaran = selectedStatusPembayaran.value;
    }

    const url = route(`report.order.export.${type}`, params);
    window.open(url, '_blank')
}
</script>

<template>
  <Head title="Laporan Order" />

  <AppLayout>
    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-xl p-6 shadow hover:shadow-lg transition">
        <h2 class="text-2xl font-semibold mb-6">Laporan Order</h2>

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
                <label class="block mb-1 font-medium">Status Order</label>
                <Select
                v-model="selectedStatus"
                :options="statusOrderOptions"
                filter
                optionLabel="nama"
                optionValue="id"
                placeholder="Pilih Status Order"
                :disabled="statusOrderOptions.length === 0"
                class="w-full"
                />
            </div>

            <div>
                <label class="block mb-1 font-medium">Status Pembayaran</label>
                <Select
                v-model="selectedStatusPembayaran"
                :options="statusPembayaranOptions"
                filter
                optionLabel="nama"
                optionValue="id"
                placeholder="Pilih Status Pembayaran"
                :disabled="statusPembayaranOptions.length === 0"
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
