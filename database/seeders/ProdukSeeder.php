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
                'deskripsi' => 'Baju futsal full printing bahan nike premium.',
                'harga' => 120000,
                'stok' => 100,
                'kategori_id' => $kategoriProdukId,
                'satuan_id' => $satuanPaketId,
                'parent_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_produk' => 'PRD00003',
                'nama' => 'Celana Futsal',
                'deskripsi' => 'Celana futsal full printing nomor celana polyflex bahan nike premium.',
                'harga' => 35000,
                'stok' => 100,
                'kategori_id' => $kategoriProdukId,
                'satuan_id' => $satuanPaketId,
                'parent_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_produk' => 'PRD00004',
                'nama' => 'Baju Bola',
                'deskripsi' => 'Baju bola full printing bahan nike premium.',
                'harga' => 120000,
                'stok' => 100,
                'kategori_id' => $kategoriProdukId,
                'satuan_id' => $satuanPaketId,
                'parent_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_produk' => 'PRD00005',
                'nama' => 'Celana Bola',
                'deskripsi' => 'Celana bola full printing nomor celana polyflex bahan nike premium.',
                'harga' => 35000,
                'stok' => 100,
                'kategori_id' => $kategoriProdukId,
                'satuan_id' => $satuanPaketId,
                'parent_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_produk' => 'PRD00006',
                'nama' => 'Baju Basket',
                'deskripsi' => 'Baju basket full printing bahan nike premium.',
                'harga' => 120000,
                'stok' => 100,
                'kategori_id' => $kategoriProdukId,
                'satuan_id' => $satuanPaketId,
                'parent_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_produk' => 'PRD00007',
                'nama' => 'Celana Basket',
                'deskripsi' => 'Celana basket full printing nomor celana polyflex bahan nike premium.',
                'harga' => 40000,
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
                'id_produk' => 'PRD00008',
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
                'id_produk' => 'PRD00009',
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
                'id_produk' => 'PRD00010',
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
