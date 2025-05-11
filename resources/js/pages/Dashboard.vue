<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const menus = [
  { label: 'Monitoring', icon: '🖥️', route: 'monitoring.index' },
//   { label: 'Order', icon: '📝', route: 'order.index' },
//   { label: 'Pembayaran', icon: '💳', route: 'pembayaran.index' },
//   { label: 'Invoice', icon: '📄', route: 'invoice.index' },
//   { label: 'Desain & Data', icon: '🎨', route: 'desain.index' },
//   { label: 'Cetak & Print', icon: '🖨️', route: 'print.index' },
//   { label: 'Press Kain', icon: '🧵', route: 'press.index' },
//   { label: 'Cutting Kain', icon: '✂️', route: 'cutting.index' },
//   { label: 'Jahit', icon: '🧶', route: 'jahit.index' },
//   { label: 'Sablon & Press Kecil', icon: '🖌️', route: 'sablon.index' },
//   { label: 'Quality Control', icon: '🔍', route: 'qc.index' },
//   { label: 'Packaging', icon: '📦', route: 'packaging.index' },
//   { label: 'Checking', icon: '✅', route: 'checking.index' },
//   { label: 'Report', icon: '📊', route: 'report.index' },
//   { label: 'Data', icon: '🗂️', route: 'data.index' },
//   { label: 'Gudang', icon: '🏬', route: 'gudang.index' },
];
</script>

<template>
  <Head title="Dashboard" />

  <AppLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
          <Link
            v-for="(menu, index) in menus"
            :key="index"
            :href="route(menu.route)"
            prefetch
            class="bg-white rounded-xl shadow hover:shadow-lg transition p-6 text-center hover:bg-gray-50 cursor-pointer"
          >
            <div class="text-4xl mb-3">{{ menu.icon }}</div>
            <div class="font-semibold text-lg text-gray-800">{{ menu.label }}</div>
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
