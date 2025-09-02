<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusPembayaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statusPembayaranId = DB::table('kategori')->insertGetId([
            'nama' => 'Status Pembayaran',
            'deskripsi' => 'Kategori Status Pembayaran',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $statusPembayaran = [
            'Menunggu ACC',
            'Selesai',
            'Batal',
        ];

        foreach ($statusPembayaran as $status) {
            DB::table('kategori')->insert([
                'nama' => $status,
                'parent_id' => $statusPembayaranId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
