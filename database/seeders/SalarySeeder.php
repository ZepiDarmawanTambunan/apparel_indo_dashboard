<?php

namespace Database\Seeders;

use App\Models\Produk;
use App\Models\Salary;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SalarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil user Super Admin
        $superAdmin = User::where('nama', 'Super Admin')->first();

        if (!$superAdmin) {
            $this->command->error('User Super Admin tidak ditemukan. Jalankan seeder Pegawai/User dulu.');
            return;
        }

        $divisiData = [
            ['divisi' => 'Press Kain', 'salary' => 30000],
            ['divisi' => 'Cutting Kain', 'salary' => 30000],
            ['divisi' => 'Jahit', 'salary' => 30000],
            ['divisi' => 'Sablon & Press Kecil', 'salary' => 20000],
        ];

        // Ambil produk dengan kategori "Produk" saja
        $produkList = Produk::whereHas('kategori', function ($q) {
            $q->where('nama', 'Produk');
        })->get();

        foreach ($produkList as $produk) {
            foreach ($divisiData as $data) {
                Salary::updateOrCreate(
                    [
                        'produk_id' => $produk->id_produk,
                        'produk_nama' => $produk->nama,
                        'divisi'    => $data['divisi'],
                    ],
                    [
                        'salary'    => $data['salary'],
                        'user_id'   => $superAdmin->id,
                        'user_nama' => $superAdmin->nama,
                    ]
                );
            }
        }

        $this->command->info('SalarySeeder selesai: hanya produk kategori "Produk" yang dapat salary.');
    }
}
