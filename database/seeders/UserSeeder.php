<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Pegawai;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pegawaiSuperadmin = Pegawai::create([
            'id_pegawai' => 'P0001',
            'nama' => 'Super Admin',
            'jabatan' => 'Owner',
            'nohp' => '081234567890',
            'email' => 'superadmin@gmail.com',
        ]);

        $userSuperadmin = User::create([
            'nama' => 'Super Admin',
            'password' => Hash::make('123456'),
            'pegawai_id' => $pegawaiSuperadmin->id_pegawai,
        ]);

        $userSuperadmin->assignRole('Super Admin');

        $pegawaiKasir = Pegawai::create([
            'id_pegawai' => 'P0002',
            'nama' => 'Kasir',
            'jabatan' => 'Kasir',
            'nohp' => '081234567890',
            'email' => 'kasir@gmail.com'
        ]);

        $userKasir = User::create([
            'nama' => 'Kasir',
            'password' => Hash::make('123456'),
            'pegawai_id' => $pegawaiKasir->id_pegawai,
        ]);

        $userKasir->assignRole('Kasir');

        $pegawaiDesainData = Pegawai::create([
            'id_pegawai' => 'P0003',
            'nama' => 'Desain Data',
            'jabatan' => 'Desain Data',
            'nohp' => '081234567890',
            'email' => 'desaindata@gmail.com',
        ]);

        $userDesainData = User::create([
            'nama' => 'Desain Data',
            'password' => Hash::make('123456'),
            'pegawai_id' => $pegawaiDesainData->id_pegawai,
        ]);

        $userDesainData->assignRole('Desain Data');

        $pegawaiCetakPrint = Pegawai::create([
            'id_pegawai' => 'P0004',
            'nama' => 'Cetak Print',
            'jabatan' => 'Cetak Print',
            'nohp' => '081234567890',
            'email' => 'cetakprint@gmail.com',
        ]);

        $userCetakPrint = User::create([
            'nama' => 'Cetak Print',
            'password' => Hash::make('123456'),
            'pegawai_id' => $pegawaiCetakPrint->id_pegawai,
        ]);

        $userCetakPrint->assignRole('Cetak Print');

        $pegawaiPressKain = Pegawai::create([
            'id_pegawai' => 'P0005',
            'nama' => 'Press Kain',
            'jabatan' => 'Press Kain',
            'nohp' => '081234567890',
            'email' => 'presskain@gmail.com',
        ]);

        $userPressKain = User::create([
            'nama' => 'Press Kain',
            'password' => Hash::make('123456'),
            'pegawai_id' => $pegawaiPressKain->id_pegawai,
        ]);

        $userPressKain->assignRole('Press Kain');

        $pegawaiCuttingKain = Pegawai::create([
            'id_pegawai' => 'P0006',
            'nama' => 'Cutting Kain',
            'jabatan' => 'Cutting Kain',
            'nohp' => '081234567890',
            'email' => 'cuttingkain@gmail.com',
        ]);

        $userCuttingKain = User::create([
            'nama' => 'Cutting Kain',
            'password' => Hash::make('123456'),
            'pegawai_id' => $pegawaiCuttingKain->id_pegawai,
        ]);

        $userCuttingKain->assignRole('Cutting Kain');

        $pegawaiJahit = Pegawai::create([
            'id_pegawai' => 'P0007',
            'nama' => 'Jahit',
            'jabatan' => 'Jahit',
            'nohp' => '081234567890',
            'email' => 'jahit@gmail.com',
        ]);

        $userJahit = User::create([
            'nama' => 'Jahit',
            'password' => Hash::make('123456'),
            'pegawai_id' => $pegawaiJahit->id_pegawai,
        ]);

        $userJahit->assignRole('Jahit');

        $pegawaiSablonPressKecil = Pegawai::create([
            'id_pegawai' => 'P0008',
            'nama' => 'Sablon & Press Kecil',
            'jabatan' => 'Sablon & Press Kecil',
            'nohp' => '081234567890',
            'email' => 'sablonpresskecil@gmail.com',
        ]);

        $userSablonPressKecil = User::create([
            'nama' => 'Sablon & Press Kecil',
            'password' => Hash::make('123456'),
            'pegawai_id' => $pegawaiSablonPressKecil->id_pegawai,
        ]);

        $userSablonPressKecil->assignRole('Sablon Press Kecil');

        $pegawaiQualityControl = Pegawai::create([
            'id_pegawai' => 'P0009',
            'nama' => 'Quality Control',
            'jabatan' => 'Quality Control',
            'nohp' => '081234567890',
            'email' => 'qualitycontrol@gmail.com',
        ]);

        $userQualityControl = User::create([
            'nama' => 'Quality Control',
            'password' => Hash::make('123456'),
            'pegawai_id' => $pegawaiQualityControl->id_pegawai,
        ]);

        $userQualityControl->assignRole('Quality Control');

        $pegawaiPackaging = Pegawai::create([
            'id_pegawai' => 'P0010',
            'nama' => 'Packaging',
            'jabatan' => 'Packaging',
            'nohp' => '081234567890',
            'email' => 'packaging@gmail.com',
        ]);

        $userPackaging = User::create([
            'nama' => 'Packaging',
            'password' => Hash::make('123456'),
            'pegawai_id' => $pegawaiPackaging->id_pegawai,
        ]);

        $userPackaging->assignRole('Packaging');

        $pegawaiChecking = Pegawai::create([
            'id_pegawai' => 'P0011',
            'nama' => 'Checking',
            'jabatan' => 'Checking',
            'nohp' => '081234567890',
            'email' => 'checking@gmail.com',
        ]);

        $userChecking = User::create([
            'nama' => 'Checking',
            'password' => Hash::make('123456'),
            'pegawai_id' => $pegawaiChecking->id_pegawai,
        ]);

        $userChecking->assignRole('Checking');

        $pegawaiGudang = Pegawai::create([
            'id_pegawai' => 'P0012',
            'nama' => 'Gudang',
            'jabatan' => 'Gudang',
            'nohp' => '081234567890',
            'email' => 'gudang@gmail.com',
        ]);

        $userGudang = User::create([
            'nama' => 'Gudang',
            'password' => Hash::make('123456'),
            'pegawai_id' => $pegawaiGudang->id_pegawai,
        ]);

        $userGudang->assignRole('Gudang');
    }
}
