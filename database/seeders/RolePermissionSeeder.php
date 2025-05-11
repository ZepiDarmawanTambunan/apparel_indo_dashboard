<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $superadmin = Role::create(['name' => 'Super Admin']);
        $kasir = Role::create(['name' => 'Kasir']);
        $desaindata = Role::create(['name' => 'Desain Data']);
        $cetakprint = Role::create(['name' => 'Cetak Print']);
        $presskain = Role::create(['name' => 'Press Kain']);
        $cuttingkain = Role::create(['name' => 'Cutting Kain']);
        $jahit = Role::create(['name' => 'Jahit']);
        $sablonpresskecil = Role::create(['name' => 'Sablon Press Kecil']);
        $qualitycontrol = Role::create(['name' => 'Quality Control']);
        $packaging = Role::create(['name' => 'Packaging']);
        $checking = Role::create(['name' => 'Checking']);
        $gudang = Role::create(['name' => 'Gudang']);

        // Create permissions
        Permission::create(['name' => 'akses-dashboard']);
        Permission::create(['name' => 'akses-monitoring']);
        Permission::create(['name' => 'akses-order']);
        Permission::create(['name' => 'akses-pembayaran']);
        Permission::create(['name' => 'akses-invoice']);
        Permission::create(['name' => 'akses-desain-data']);
        Permission::create(['name' => 'akses-cetak-print']);
        Permission::create(['name' => 'akses-press-kain']);
        Permission::create(['name' => 'akses-cutting-kain']);
        Permission::create(['name' => 'akses-jahit']);
        Permission::create(['name' => 'akses-sablon-press-kecil']);
        Permission::create(['name' => 'akses-quality-control']);
        Permission::create(['name' => 'akses-packaging']);
        Permission::create(['name' => 'akses-checking']);
        Permission::create(['name' => 'akses-gudang']);
        Permission::create(['name' => 'akses-data']);

        // Assign permissions to roles
        $superadmin->givePermissionTo([
            'akses-dashboard',
            'akses-monitoring',
            'akses-order',
            'akses-pembayaran',
            'akses-invoice',
            'akses-desain-data',
            'akses-cetak-print',
            'akses-press-kain',
            'akses-cutting-kain',
            'akses-jahit',
            'akses-sablon-press-kecil',
            'akses-quality-control',
            'akses-packaging',
            'akses-checking',
            'akses-gudang',
            'akses-data'
        ]);

        $kasir->givePermissionTo([
            'akses-dashboard',
            'akses-monitoring',
            'akses-order',
            'akses-pembayaran',
            'akses-invoice',
            'akses-gudang'
        ]);

        $desaindata->givePermissionTo([
            'akses-dashboard',
            'akses-monitoring',
            'akses-order',
            'akses-invoice',
            'akses-desain-data',
            'akses-gudang'
        ]);

        $cetakprint->givePermissionTo([
            'akses-dashboard',
            'akses-monitoring',
            'akses-order',
            'akses-invoice',
            'akses-cetak-print',
            'akses-gudang'
        ]);

        $presskain->givePermissionTo([
            'akses-dashboard',
            'akses-monitoring',
            'akses-order',
            'akses-invoice',
            'akses-press-kain',
            'akses-gudang'
        ]);

        $cuttingkain->givePermissionTo([
            'akses-dashboard',
            'akses-monitoring',
            'akses-order',
            'akses-invoice',
            'akses-cutting-kain',
            'akses-gudang'
        ]);

        $jahit->givePermissionTo([
            'akses-dashboard',
            'akses-monitoring',
            'akses-order',
            'akses-invoice',
            'akses-jahit',
            'akses-gudang'
        ]);

        $sablonpresskecil->givePermissionTo([
            'akses-dashboard',
            'akses-monitoring',
            'akses-order',
            'akses-invoice',
            'akses-sablon-press-kecil',
            'akses-gudang'
        ]);

        $qualitycontrol->givePermissionTo([
            'akses-dashboard',
            'akses-monitoring',
            'akses-order',
            'akses-invoice',
            'akses-quality-control',
            'akses-gudang'
        ]);

        $packaging->givePermissionTo([
            'akses-dashboard',
            'akses-monitoring',
            'akses-order',
            'akses-invoice',
            'akses-packaging',
            'akses-gudang'
        ]);

        $checking->givePermissionTo([
            'akses-dashboard',
            'akses-monitoring',
            'akses-order',
            'akses-invoice',
            'akses-checking',
            'akses-gudang'
        ]);

        $gudang->givePermissionTo([
            'akses-dashboard',
            'akses-monitoring',
            'akses-order',
            'akses-invoice',
            'akses-gudang'
        ]);
    }
}
