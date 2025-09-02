<?php

namespace App\Http\Controllers;

use App\Models\LaporanKerusakan;
use App\Models\Order;
use Inertia\Inertia;
use Illuminate\Http\Request;

use function PHPSTORM_META\map;

class CheckingController extends Controller
{
    public function index()
    {
        $breadcrumbs = [
            ['title' => 'Checking', 'href' => route('checking.index')],
        ];

        $laporanKerusakan = LaporanKerusakan::with(['order', 'status', 'statusChecking'])
            ->whereHas('order')
            ->join('order', 'laporan_kerusakan.order_id', '=', 'order.id_order')
            ->orderBy('order.tgl_deadline')
            ->orderBy('laporan_kerusakan.order_id')
            ->select('laporan_kerusakan.*')
            ->get();

        return Inertia::render('checking/Index', [
            'breadcrumbs' => $breadcrumbs,
            'laporanKerusakan' => $laporanKerusakan
        ]);
    }

    public function show($id)
    {
        $laporanKerusakan = LaporanKerusakan::with([
            'order.status',
            'order.statusPembayaran',
            'status',
            'statusChecking',
        ])->findOrFail($id);

        $divisi_bertanggung_jawab = [
            'Cetak Print',
            'Press Kain',
            'Cutting Kain',
            'Jahit',
            'Sablon Press',
            'QC',
            'Packaging'
        ];

        return Inertia::render('checking/Show', [
            'divisi_bertanggung_jawab' => $divisi_bertanggung_jawab,
            'laporan_kerusakan' => $laporanKerusakan,
        ]);
    }
}
