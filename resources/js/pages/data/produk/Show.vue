<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';

interface Produk {
  id_produk: string;
  nama: string;
  deskripsi?: string | null;
  harga: number;
  stok: number;
  kategori?: string | null;
  satuan?: string | null;
  cover_foto_url?: string | null;
  galeri_foto_urls: string[];
  created_at: string;
  updated_at: string;
}

const { props } = usePage<{ produk: Produk }>();
const produk = props.produk;

// Fallbacks supaya tidak error jika null atau undefined
if (!produk.galeri_foto_urls) produk.galeri_foto_urls = [];
if (produk.cover_foto_url === undefined) produk.cover_foto_url = null;
</script>

<template>
  <Head title="Detail Produk" />

  <AppLayout>
    <div class="py-6 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-2xl shadow-md p-6 grid md:grid-cols-2 gap-6">
        <!-- Cover Foto -->
        <div class="flex justify-center">
          <img
            :src="produk.cover_foto_url ?? 'https://via.placeholder.com/300?text=No+Cover'"
            alt="Cover Foto Produk"
            class="rounded-xl w-full max-w-sm object-cover shadow-md"
          />
        </div>

        <!-- Informasi Produk -->
        <div class="space-y-4">
          <h1 class="text-3xl font-bold text-gray-900">{{ produk.nama }}</h1>

            <div class="text-lg text-gray-800 space-y-2 mt-4">
              <div><strong>Harga:</strong> Rp{{ produk.harga.toLocaleString() }}</div>
              <!-- <div><strong>Stok:</strong> {{ produk.stok }}</div> -->
              <div><strong>Kategori:</strong> {{ produk.kategori ?? '-' }}</div>
              <div><strong>Satuan:</strong> {{ produk.satuan ?? '-' }}</div>
            </div>

            <p v-if="produk.deskripsi" class="text-gray-700">{{ produk.deskripsi }}</p>
            <p v-else class="text-gray-400 italic">Deskripsi belum tersedia.</p>

          <div class="text-sm text-gray-500 space-y-1 mt-4">
            <div>Dibuat: {{ produk.created_at }}</div>
            <div>Diubah: {{ produk.updated_at }}</div>
          </div>
        </div>
      </div>

      <!-- Galeri Foto -->
      <div v-if="produk.galeri_foto_urls.length" class="mt-8 bg-white rounded-2xl shadow-md p-6">
        <h2 class="text-xl font-semibold mb-4">Galeri Foto</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
          <img
            v-for="(foto, idx) in produk.galeri_foto_urls"
            :key="idx"
            :src="foto"
            alt="Foto Galeri Produk"
            class="rounded-lg shadow object-cover w-full aspect-square"
          />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
