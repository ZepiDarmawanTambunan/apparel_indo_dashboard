<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProdukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil ID kategori Produk & Tambahan
        $kategoriProdukId = DB::table('kategori')->where('nama', 'Produk')->value('id');
        $kategoriTambahanId = DB::table('kategori')->where('nama', 'Tambahan')->value('id');

        // Ambil ID satuan
        $satuanPaketId = DB::table('satuan')->where('nama', 'Paket')->value('id');
        $satuanPcsId = DB::table('satuan')->where('nama', 'Pcs')->value('id');

         // Produk utama
        $produk = [
            [
                'id_produk' => 'PRD00001',
                'nama' => 'Jaket Full Printing',
                'deskripsi' => 'Tersedia tipe: Hoodie, Zipper, Training, Bomber. Bahan jakert premium sport, rib poly. minimal order 10 pcs',
                'harga' => 185000,
                'stok' => 100,
                'kategori_id' => $kategoriProdukId,
                'satuan_id' => $satuanPaketId,
                'parent_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_produk' => 'PRD00002',
                'nama' => 'Baju Futsal',
                'deskripsi' => 'Baju futsal full printing, celana non-print, nomor celana polyflex, bahan nike premium.',
                'harga' => 150000,
                'stok' => 100,
                'kategori_id' => $kategoriProdukId,
                'satuan_id' => $satuanPaketId,
                'parent_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_produk' => 'PRD00003',
                'nama' => 'Baju Bola',
                'deskripsi' => 'Baju bola full printing, celana non-print, nomor celana polyflex, bahan nike premium.',
                'harga' => 160000,
                'stok' => 100,
                'kategori_id' => $kategoriProdukId,
                'satuan_id' => $satuanPaketId,
                'parent_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_produk' => 'PRD00004',
                'nama' => 'Baju Basket',
                'deskripsi' => 'Baju basket full printing, celana non-print, nomor celana polyflex, bahan nike premium.',
                'harga' => 165000,
                'stok' => 100,
                'kategori_id' => $kategoriProdukId,
                'satuan_id' => $satuanPaketId,
                'parent_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('produk')->insert($produk);

        // Produk tambahan
        $tambahan = [
            [
                'id_produk' => 'PRD00005',
                'nama' => 'LOGO 3D CREST',
                'deskripsi' => 'Tambahan logo 3d crest.',
                'harga' => 25000,
                'stok' => 100,
                'kategori_id' => $kategoriTambahanId,
                'satuan_id' => $satuanPcsId,
                'parent_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_produk' => 'PRD00006',
                'nama' => 'XXL keatas',
                'deskripsi' => 'Tambahan ukuran.',
                'harga' => 10000,
                'stok' => 100,
                'kategori_id' => $kategoriTambahanId,
                'satuan_id' => $satuanPcsId,
                'parent_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_produk' => 'PRD00007',
                'nama' => 'Bahan Selain Nike',
                'deskripsi' => 'Request bahan selain nike.',
                'harga' => 10000,
                'stok' => 100,
                'kategori_id' => $kategoriTambahanId,
                'satuan_id' => $satuanPcsId,
                'parent_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('produk')->insert($tambahan);
    }
}
