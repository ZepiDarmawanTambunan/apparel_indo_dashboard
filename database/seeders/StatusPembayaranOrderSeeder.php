<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusPembayaranOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statusPembayaranOrderId = DB::table('kategori')->insertGetId([
            'nama' => 'Status Pembayaran Order',
            'deskripsi' => 'Kategori Status Pembayaran Order',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $statusPembayaranOrder = [
            'Belum Bayar',
            'DP Awal',
            'DP Produksi',
            'Lunas',
        ];

        foreach ($statusPembayaranOrder as $status) {
            DB::table('kategori')->insert([
                'nama' => $status,
                'parent_id' => $statusPembayaranOrderId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
