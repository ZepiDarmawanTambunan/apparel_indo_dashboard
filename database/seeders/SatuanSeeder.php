<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SatuanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['nama' => 'Paket', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Pcs', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('satuan')->insert($data);
    }
}
