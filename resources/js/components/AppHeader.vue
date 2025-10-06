<script setup lang="ts">
import { computed, ref } from 'vue';
import { onClickOutside } from '@vueuse/core';
import { Link, router, usePage } from '@inertiajs/vue3';

// Tipe Pegawai
interface Pegawai {
  id: number;
  nama: string;
  jabatan?: string;
  foto_url?: string;
}

// Tipe User dari auth
interface AuthUser {
  id: number;
  nama: string;
  role?: string;
  pegawai?: Pegawai | null;
}

interface PageProps {
  auth: {
    user: AuthUser;
  };
  [key: string]: unknown;
}

// Ambil props dari Inertia
const page = usePage<PageProps>();
const user = computed(() => page.props.auth.user);

// Dropdown
const dropdownOpen = ref(false);
const dropdownRef = ref(null);

onClickOutside(dropdownRef, () => {
  dropdownOpen.value = false;
});

const toggleDropdown = () => {
  dropdownOpen.value = !dropdownOpen.value;
};

const handleLogout = () => {
  router.flushAll(); // Optional, tergantung kebutuhan logout
};
</script>

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
          <span class="text-gray-800 font-medium hidden sm:block">{{ user.nama }}</span>
          <img
            :src="user.pegawai?.foto_url || '/images/user.png'"
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
              :href="route('profil.index')"
              class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition cursor-pointer"
            >
              Profil
            </Link>
            <Link
                method="post"
                :href="route('logout')"
                replace
                @click="handleLogout"
                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition cursor-pointer">
                Keluar
            </Link>
          </div>
        </transition>
      </div>
    </div>
  </header>
</template>

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
