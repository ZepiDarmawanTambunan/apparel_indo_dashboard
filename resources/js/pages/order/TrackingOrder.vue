<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps<{
  current_status: string;
}>();

const statusOrder = [
    'Menunggu DP Awal',
    'Menunggu ACC DP Awal',
    'Desain Data',
    'Menunggu DP Produksi',
    'Menunggu ACC DP Produksi',
    'Cetak & Print',
    'Press Kain',
    'Cutting Kain',
    'Jahit',
    'Sablon & Press Kecil',
    'QC',
    'Packaging',
    'Menunggu Tagihan Lunas',
    'Menunggu ACC Lunas',
    'Selesai',
    'Batal',
];

function goBack() {
  router.visit(route('order.index'), { replace: true });
}
</script>

<template>
  <Head title="Tracking Order" />
  <AppLayout>
    <div class="py-8 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-xl p-6 shadow hover:shadow-lg transition">
        <h2 class="text-2xl font-bold mb-8 text-center">Tracking Order</h2>

        <div class="relative">
          <!-- Vertical Line -->
          <div class="absolute top-0 left-1/2 transform -translate-x-1/2 h-full w-1 bg-gray-300 z-0"></div>

          <!-- Timeline Items -->
          <div class="flex flex-col space-y-12 relative z-10">
            <div
              v-for="(status, index) in statusOrder"
              :key="index"
              class="flex items-center justify-between"
            >
              <div class="w-1/2 pr-8 text-right"></div>

              <!-- Dot -->
              <div class="relative">
                <div
                  class="w-6 h-6 rounded-full border-4"
                  :class="status === current_status ? 'bg-blue-600 border-white shadow-lg' : 'bg-gray-300 border-white'"
                ></div>
              </div>

              <!-- Label -->
              <div class="w-1/2 pl-8">
                <div
                  class="text-sm font-semibold text-gray-800 bg-gray-100 px-4 py-2 rounded-xl shadow hover:bg-gray-200 transition"
                >
                  {{ status }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-8 text-center">
        <button
            @click="goBack"
            class="inline-block w-full px-4 py-3 rounded-2xl bg-red-600 text-white font-semibold hover:bg-red-700 transition cursor-pointer"
        >
            Kembali
        </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
