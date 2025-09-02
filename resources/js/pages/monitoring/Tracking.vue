<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps<{
  jumlah_per_status: Record<string, number>;
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

const orderCounts = statusOrder.map((status) => props.jumlah_per_status[status] ?? 0);
</script>

<template>
  <Head title="Tracking" />
  <AppLayout>
    <div class="py-8 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl p-6 shadow hover:shadow-lg transition">
            <h2 class="text-2xl font-bold mb-8 text-center">Tracking Order</h2>
            <div class="relative">
            <!-- Vertical Line Center -->
            <div class="absolute top-0 left-1/2 transform -translate-x-1/2 h-full w-1 bg-gray-300 z-0"></div>

            <!-- Timeline Items -->
            <div class="flex flex-col space-y-12 relative z-10">
              <div
                v-for="(status, index) in statusOrder"
                :key="index"
                class="flex items-center justify-between"
              >
                <!-- Left: Jumlah Order -->
                <div class="w-1/2 pr-8 text-right">
                  <div
                    class="inline-block bg-blue-100 text-blue-800 text-sm px-3 py-1 rounded-full shadow-sm hover:scale-105 transition transform"
                    :title="`${orderCounts[index]} order`"
                  >
                    {{ orderCounts[index] }} order
                  </div>
                </div>

                <!-- Center Dot -->
                <div class="relative">
                  <div
                    class="w-6 h-6 rounded-full border-4"
                    :class="orderCounts[index] > 0 ? 'bg-blue-500 border-white shadow-lg' : 'bg-gray-300 border-white'"
                  ></div>
                </div>

                <!-- Right: Status -->
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
        </div>
    </div>
  </AppLayout>
</template>
