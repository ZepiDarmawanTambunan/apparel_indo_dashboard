<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriPembayaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategoriPembayaranId = DB::table('kategori')->insertGetId([
            'nama' => 'Kategori Pembayaran',
            'deskripsi' => 'Kategori Pembayaran',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $kategoriPembayaran = [
            'DP Awal',
            'DP Produksi',
            'Lunas',
        ];

        foreach ($kategoriPembayaran as $kategori) {
            DB::table('kategori')->insert([
                'nama' => $kategori,
                'parent_id' => $kategoriPembayaranId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
