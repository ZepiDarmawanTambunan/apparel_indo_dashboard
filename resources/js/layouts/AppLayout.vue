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
        </div>
      </footer>
    </div>
  </div>
</template>

<script setup lang="ts">
import AppHeader from '@/components/AppHeader.vue'
import { usePage } from '@inertiajs/vue3'
import { watch } from 'vue'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

interface ToastMessage {
  type: 'success' | 'error' | 'info' | 'warning'
  message: string
}

const page = usePage<{ toast?: ToastMessage }>()

watch(
  () => page.props.toast,
  (toastMessage) => {
    if (toastMessage) {
      toast[toastMessage.type](toastMessage.message, {
        autoClose: 3000,
        position: 'top-right',
      })
    }
  },
  { immediate: true }
)
</script>
