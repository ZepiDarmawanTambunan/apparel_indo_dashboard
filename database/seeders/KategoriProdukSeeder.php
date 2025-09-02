<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriProdukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insert Parent Kategori Produk
        $kategoriProdukParentId = DB::table('kategori')->insertGetId([
            'nama' => 'Kategori Produk',
            'deskripsi' => 'Kategori utama untuk jenis produk konveksi',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert Turunan: Produk dan Tambahan
        $data = [
            [
                'nama' => 'Produk',
                'deskripsi' => 'Kategori produk utama konveksi',
                'parent_id' => $kategoriProdukParentId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Tambahan',
                'deskripsi' => 'Kategori tambahan untuk produksi',
                'parent_id' => $kategoriProdukParentId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('kategori')->insert($data);
    }
}
