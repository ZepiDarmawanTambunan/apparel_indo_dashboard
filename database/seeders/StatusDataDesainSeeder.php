<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusDataDesainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $parentId = DB::table('kategori')->insertGetId([
            'nama' => 'Status Data Desain',
            'deskripsi' => 'Status untuk proses data desain order',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $statuses = [
            'Belum Diterima',
            'Proses',
            'Menunggu Feedback',
            'Revisi',
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
