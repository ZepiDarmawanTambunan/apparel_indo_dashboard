<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insert parent categories
        $statusOrderId = DB::table('kategori')->insertGetId([
            'nama' => 'Status Order',
            'deskripsi' => 'Kategori status alur produksi order',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $statusOrder = [
            'Draft',
            'Menunggu DP Awal',
            'Menunggu ACC DP Awal',
            'Desain Data',
            'Menunggu DP Produksi',
            'Menunggu ACC DP Produksi',
            'Cetak & Print',
            'Press Kain',
            'Cutting Kain',
            'Jahit',
            'Sablon & Press Kecil',
            'QC',
            'Packaging',
            'Menunggu Tagihan Lunas',
            'Menunggu ACC Lunas',
            'Selesai',
            'Batal',
        ];

        foreach ($statusOrder as $status) {
            DB::table('kategori')->insert([
                'nama' => $status,
                'parent_id' => $statusOrderId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
