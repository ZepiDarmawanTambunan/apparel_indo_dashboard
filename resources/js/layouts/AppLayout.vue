<template>
  <div class="h-screen bg-gray-100">
    <div class="flex flex-col">
      <header class="bg-blue-600 text-white p-4">
        <slot name="header"></slot>
      </header>

      <main class="flex-1 p-4">
        <slot></slot>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
    import { defineProps } from 'vue';
    import type { BreadcrumbItemType } from '@/types';

    const props = withDefaults(defineProps<{
            breadcrumbs?: BreadcrumbItemType[];
        }>(),
        { breadcrumbs: () => [], }
    );
</script>
