<template>
  <header class="bg-white shadow px-4 py-6">
    <div class="flex items-center justify-between">
      <!-- Kiri: Logo & Nama Aplikasi -->
      <div class="flex items-center gap-4">
        <Link :href="route('dashboard')" prefetch class="flex items-center gap-4 cursor-pointer">
          <img src="/images/logo.JPG" alt="Logo" class="h-10 w-10 object-contain rounded-md" />
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Apparel Indo
          </h2>
        </Link>
      </div>

      <!-- Kanan: User Info -->
      <div class="relative" ref="dropdownRef">
        <div
          class="flex items-center gap-3 cursor-pointer select-none"
          @click="toggleDropdown"
        >
          <span class="text-gray-800 font-medium hidden sm:block">{{ user }}</span>
          <img
            src="/images/user.png"
            alt="User"
            class="h-10 w-10 rounded-full object-cover border"
          />
        </div>

        <!-- Dropdown Menu -->
        <transition name="fade">
          <div
            v-if="dropdownOpen"
            class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg ring-1 ring-black/10 z-50"
          >
            <Link
              href="/akun"
              class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition cursor-pointer"
            >
              Akun
            </Link>
            <Link method="post" :href="route('logout')" @click="handleLogout" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition cursor-pointer">
                Logout
            </Link>
          </div>
        </transition>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { onClickOutside } from '@vueuse/core';
import { Link, router } from '@inertiajs/vue3';
import type { User } from '@/types';

const page = usePage();
const user = computed(() => page.props.auth?.user?.nama ?? 'Pengguna');

const dropdownOpen = ref(false);
const dropdownRef = ref(null);

onClickOutside(dropdownRef, () => {
  dropdownOpen.value = false;
});

const toggleDropdown = () => {
  dropdownOpen.value = !dropdownOpen.value;
};

const handleLogout = () => {
    router.flushAll();
};
</script>

<style scoped>
    .fade-enter-active,
    .fade-leave-active {
        transition: opacity 0.2s ease;
    }
    .fade-enter-from,
    .fade-leave-to {
        opacity: 0;
    }
</style>
