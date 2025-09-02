<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StokProdukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // Ambil kategori_id untuk Barang Masuk (Stok Awal)
        $kategoriStokMasukId = DB::table('kategori')->where('nama', 'Barang Masuk')->value('id');

        if (!$kategoriStokMasukId) {
            Log::warning('Kategori Barang Masuk tidak ditemukan. Seeder StokProdukSeeder dibatalkan.');
            return;
        }

        // Ambil semua produk
        $produkList = DB::table('produk')->get();

        foreach ($produkList as $produk) {
            DB::table('stok_produk')->insert([
                'produk_id' => $produk->id_produk,
                'stok_before' => 0,
                'stok_after' => $produk->stok,
                'kategori_id' => $kategoriStokMasukId,
                'keterangan' => 'Stok awal input dari seeder.',
                'tgl_jam' => now(),
            ]);
        }
    }
}
