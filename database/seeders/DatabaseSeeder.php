<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Termwind\Components\Hr;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            // FOR MODUL USER
            RolePermissionSeeder::class,
            UserSeeder::class,

            // FOR MODEL ORDER
            StatusOrderSeeder::class,
            StatusPembayaranOrderSeeder::class,

            // FOR MODEL PRODUK
            SatuanSeeder::class,
            KategoriProdukSeeder::class,
            KategoriStokProdukSeeder::class,
            ProdukSeeder::class,
            HargaProdukSeeder::class,
            StokProdukSeeder::class,

            // FOR MODEL PEMBAYARAN
            StatusPembayaranSeeder::class,
            KategoriPembayaranSeeder::class,

            // FOR MODEL INVOICE
            StatusInvoiceSeeder::class,
            KategoriInvoiceSeeder::class,

            // FOR MODEL DESAIN DATA
            StatusDataDesainSeeder::class,

            // FOR MODEL CETAK PRINT
            StatusCetakPrintSeeder::class,

            // FOR MODEL LAPORAN KERUSAKAN
            StatusLaporanKerusakanSeeder::class,
            StatusCheckingSeeder::class,

            // FOR MODEL PRESS KAIN
            StatusPressKainSeeder::class,

            // FOR MODEL CUTTING KAIN
            StatusCuttingKainSeeder::class,

            // FOR MODEL JAHIT
            StatusJahitSeeder::class,

            // FOR MODEL SABLON PRESS
            StatusSablonPressSeeder::class,

            // FOR MODEL QC
            StatusQCSeeder::class,

            // FOR MODEL PACKAGING
            StatusPackagingSeeder::class,

            // FOR MODEL SALARY
            SalarySeeder::class,
        ]);
    }
}
