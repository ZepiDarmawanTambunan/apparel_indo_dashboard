<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const menus = [
  { label: 'Tgl Deadline', icon: '📅', route: 'monitoring.deadline' },
  { label: 'Tracking Order', icon: '📦', route: 'monitoring.tracking' },
];
</script>

<template>
  <Head title="Monitoring" />

  <AppLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
          <Link
            v-for="(menu, index) in menus"
            :key="index"
            :href="route(menu.route)"
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
