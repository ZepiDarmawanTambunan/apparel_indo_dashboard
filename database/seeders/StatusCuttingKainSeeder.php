<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusCuttingKainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $parentId = DB::table('kategori')->insertGetId([
            'nama' => 'Status Cutting Kain',
            'deskripsi' => 'Status untuk proses cutting kain order',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $statuses = [
            'Belum Diterima',
            'Proses',
            'Selesai',
            'Batal',
        ];

        foreach ($statuses as $status) {
            DB::table('kategori')->insert([
                'nama' => $status,
                'parent_id' => $parentId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
