<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<{
  permissions: string[];
}>();

const allMenus = [
  { label: 'Monitoring', icon: '🖥️', route: 'monitoring.index', permission: 'akses-monitoring' },
  { label: 'Order', icon: '📝', route: 'order.index', permission: 'akses-order' },
  { label: 'Pembayaran', icon: '💳', route: 'pembayaran.index', permission: 'akses-pembayaran' },
  { label: 'Invoice', icon: '📄', route: 'invoice.index', permission: 'akses-invoice' },
  { label: 'Desain & Data', icon: '🎨', route: 'desain-data.index', permission: 'akses-desain-data' },
  { label: 'Cetak & Print', icon: '🖨️', route: 'cetak-print.index', permission: 'akses-cetak-print' },
  { label: 'Press Kain', icon: '🧵', route: 'press-kain.index', permission: 'akses-press-kain' },
  { label: 'Cutting Kain', icon: '✂️', route: 'cutting-kain.index', permission: 'akses-cutting-kain' },
  { label: 'Jahit', icon: '🧶', route: 'jahit.index', permission: 'akses-jahit' },
  { label: 'Sablon & Press Kecil', icon: '🖌️', route: 'sablon-press-kecil.index', permission: 'akses-sablon-press-kecil' },
  { label: 'Quality Control', icon: '🔍', route: 'qc.index', permission: 'akses-quality-control' },
  { label: 'Packaging', icon: '📦', route: 'packaging.index', permission: 'akses-packaging' },
  { label: 'Checking', icon: '✅', route: 'checking.index', permission: 'akses-checking' },
  { label: 'Report', icon: '📊', route: 'report.index', permission: 'akses-data' },
  { label: 'Data', icon: '🗂️', route: 'data.index', permission: 'akses-data' },
];

const menus = allMenus.filter(menu =>
  props.permissions.includes(menu.permission)
);
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
