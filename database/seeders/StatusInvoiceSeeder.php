<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusInvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statusInvoiceId = DB::table('kategori')->insertGetId([
            'nama' => 'Status Invoice',
            'deskripsi' => 'Status Invoice',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $statusInvoice = [
            'Proses',
            'Selesai',
            'Batal',
            'Tolak',
        ];

        foreach ($statusInvoice as $status) {
            DB::table('kategori')->insert([
                'nama' => $status,
                'parent_id' => $statusInvoiceId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
