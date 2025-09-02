<?php

namespace App\Http\Controllers;

use App\Models\DataDesain;
use App\Models\Kategori;
use App\Models\Order;
use App\Models\Pembayaran;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonitoringController extends Controller
{
    public function index()
    {
        return Inertia::render('monitoring/Index');
    }

    public function order()
    {
        $pembayarans = Pembayaran::with(['status', 'order', 'kategori'])
            ->whereHas('status', function ($query) {
                $query->where('nama', 'Menunggu ACC');
            })
            ->latest()
            ->get();

        $desainDatas = DataDesain::with(['status', 'order'])
            ->whereHas('status', function ($query) {
                $query->whereIn('nama', ['Belum Diterima', 'Proses']);
            })
            ->latest()
            ->get();

        $orders = Order::with('status')
            ->whereHas('status', function ($query) {
                $query->whereIn('nama', [
                    'Cetak & Print',
                    'Press Kain',
                    'Cutting Kain',
                    'Jahit',
                    'Sablon & Press Kecil',
                    'QC',
                    'Packaging',
                ]);
            })
            ->latest()
            ->get();

        return Inertia::render('monitoring/Order', [
            'pembayarans' => $pembayarans,
            'desainDatas' => $desainDatas,
            'orders' => $orders
        ]);
    }

    public function tracking()
    {
        // Ambil ID satu-satu sesuai cara kamu
        $statusOrderMenungguDPAwal = Kategori::getKategoriId('Status Order', 'Menunggu DP Awal');
        $statusOrderMenungguACCDPAwal = Kategori::getKategoriId('Status Order', 'Menunggu ACC DP Awal');
        $statusOrderDesainData = Kategori::getKategoriId('Status Order', 'Desain Data');
        $statusOrderMenungguDPProduksi = Kategori::getKategoriId('Status Order', 'Menunggu DP Produksi');
        $statusOrderMenungguACCDPProduksi = Kategori::getKategoriId('Status Order', 'Menunggu ACC DP Produksi');

        $statusOrderCetakPrint = Kategori::getKategoriId('Status Order', 'Cetak & Print');
        $statusOrderPressKain = Kategori::getKategoriId('Status Order', 'Press Kain');
        $statusOrderCuttingKain = Kategori::getKategoriId('Status Order', 'Cutting Kain');
        $statusOrderJahit = Kategori::getKategoriId('Status Order', 'Jahit');
        $statusOrderSablonPressKecil = Kategori::getKategoriId('Status Order', 'Sablon & Press Kecil');
        $statusOrderQC = Kategori::getKategoriId('Status Order', 'QC');
        $statusOrderPackaging = Kategori::getKategoriId('Status Order', 'Packaging');

        $statusOrderMenungguTagihanLunas = Kategori::getKategoriId('Status Order', 'Menunggu Tagihan Lunas');
        $statusOrderMenungguACCLunas = Kategori::getKategoriId('Status Order', 'Menunggu ACC Lunas');
        $statusOrderSelesai = Kategori::getKategoriId('Status Order', 'Selesai');
        $statusOrderBatal = Kategori::getKategoriId('Status Order', 'Batal');

        // Hitung jumlah order per status
        $jumlahPerStatus = [
            'Menunggu DP Awal' => Order::where('status_id', $statusOrderMenungguDPAwal)->count(),
            'Menunggu ACC DP Awal' => Order::where('status_id', $statusOrderMenungguACCDPAwal)->count(),
            'Desain Data' => Order::where('status_id', $statusOrderDesainData)->count(),
            'Menunggu DP Produksi' => Order::where('status_id', $statusOrderMenungguDPProduksi)->count(),
            'Menunggu ACC DP Produksi' => Order::where('status_id', $statusOrderMenungguACCDPProduksi)->count(),
            'Cetak & Print' => Order::where('status_id', $statusOrderCetakPrint)->count(),
            'Press Kain' => Order::where('status_id', $statusOrderPressKain)->count(),
            'Cutting Kain' => Order::where('status_id', $statusOrderCuttingKain)->count(),
            'Jahit' => Order::where('status_id', $statusOrderJahit)->count(),
            'Sablon & Press Kecil' => Order::where('status_id', $statusOrderSablonPressKecil)->count(),
            'QC' => Order::where('status_id', $statusOrderQC)->count(),
            'Packaging' => Order::where('status_id', $statusOrderPackaging)->count(),
            'Menunggu Tagihan Lunas' => Order::where('status_id', $statusOrderMenungguTagihanLunas)->count(),
            'Menunggu ACC Lunas' => Order::where('status_id', $statusOrderMenungguACCLunas)->count(),
            'Selesai' => Order::where('status_id', $statusOrderSelesai)->count(),
            'Batal' => Order::where('status_id', $statusOrderBatal)->count(),
        ];

        return Inertia::render('monitoring/Tracking', [
            'jumlah_per_status' => $jumlahPerStatus,
        ]);
    }
}
