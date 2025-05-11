<template>
  <div class="bg-gray-100">
    <div class="flex flex-col">
        <AppHeader />

      <main class="flex-1 p-4">
        <slot />
      </main>

    <footer class="bg-white shadow px-4 py-6">
        <div class="text-center text-gray-600 text-sm space-y-1">
            <div>&copy; 2023 Apparel Indo. All rights reserved.</div>
            <!-- <div>Developed by <a href="https://your-portfolio-link.com" target="_blank" class="text-blue-600 hover:underline">Zepi Darmawan</a></div> -->
        </div>
    </footer>
    </div>
  </div>
</template>

<script setup lang="ts">
    import AppHeader from '@/components/AppHeader.vue';
</script>
