<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriInvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategoriInvoiceId = DB::table('kategori')->insertGetId([
            'nama' => 'Kategori Invoice',
            'deskripsi' => 'Kategori Invoice',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $kategoriInvoice = [
            'DP Awal',
            'DP Produksi',
            'Lunas',
        ];

        foreach ($kategoriInvoice as $kategori) {
            DB::table('kategori')->insert([
                'nama' => $kategori,
                'parent_id' => $kategoriInvoiceId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
