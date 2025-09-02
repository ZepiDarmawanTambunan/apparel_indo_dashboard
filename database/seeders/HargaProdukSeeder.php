<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HargaProdukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua produk dari tabel produk
        $produkList = DB::table('produk')->get();

        foreach ($produkList as $produk) {
            DB::table('harga_produk')->insert([
                'produk_id' => $produk->id_produk,
                'harga_before' => 0, // Karena ini harga awal
                'harga_after' => $produk->harga,
                'tgl_jam' => now(),
            ]);
        }
    }
}
