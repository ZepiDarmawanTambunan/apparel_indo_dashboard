<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriStokProdukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategoriStokProdukParentId = DB::table('kategori')->insertGetId([
            'nama' => 'Kategori Stok Produk',
            'deskripsi' => 'Kategori untuk jenis perubahan stok produk.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $kategoriStok = [
            'Barang Masuk',
            'Barang Keluar',
            'Edit Order',
            'Batal Order',
            'Refund',
        ];

        foreach ($kategoriStok as $kategori) {
            DB::table('kategori')->insert([
                'nama' => $kategori,
                'parent_id' => $kategoriStokProdukParentId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
