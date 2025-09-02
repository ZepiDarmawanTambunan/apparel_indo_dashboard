<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusCheckingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $parentId = DB::table('kategori')->insertGetId([
            'nama' => 'Status Checking',
            'deskripsi' => 'Status untuk checking',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $statuses = [
            'Pending',
            'Selesai',
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
