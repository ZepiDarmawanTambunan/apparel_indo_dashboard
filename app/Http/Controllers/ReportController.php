<?php

namespace App\Http\Controllers;

use App\Exports\CuttingKainExport;
use App\Exports\JahitExport;
use App\Exports\LaporanKerusakanExport;
use App\Exports\OrderExport;
use App\Exports\ProdukExport;
use App\Models\Kategori;
use App\Models\LaporanKerusakan;
use App\Models\Order;
use App\Models\Produk;
use App\Models\RiwayatCuttingKain;
use App\Models\RiwayatJahit;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $breadcrumbs = [
            ['title' => 'Report', 'href' => route('report.index')],
        ];

        return Inertia::render('report/Index', [
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    public function order()
    {
        $breadcrumbs = [
            ['title' => 'Report', 'href' => route('report.index')],
        ];

        $statusOrder = Kategori::whereHas('parent', function ($query) {
            $query->whereRaw('LOWER(nama) = ?', ['status order']);
        })->get();

        $statusPembayaran = Kategori::whereHas('parent', function ($query) {
            $query->whereRaw('LOWER(nama) = ?', ['status pembayaran order']);
        })->get();

        return Inertia::render('report/Order', [
            'breadcrumbs' => $breadcrumbs,
            'status_order' => $statusOrder,
            'status_pembayaran' => $statusPembayaran,
        ]);
    }

    public function kerusakan()
    {
        $breadcrumbs = [
            ['title' => 'Report', 'href' => route('report.index')],
        ];

        $statusKerusakan = Kategori::whereHas('parent', function ($query) {
            $query->whereRaw('LOWER(nama) = ?', ['status laporan kerusakan']);
        })->get();

        $statusChecking = Kategori::whereHas('parent', function ($query) {
            $query->whereRaw('LOWER(nama) = ?', ['status checking']);
        })->get();

        return Inertia::render('report/Kerusakan', [
            'breadcrumbs' => $breadcrumbs,
            'status_kerusakan' => $statusKerusakan,
            'status_checking' => $statusChecking,
        ]);
    }

    public function produk()
    {
        $breadcrumbs = [
            ['title' => 'Report', 'href' => route('report.index')],
        ];

        $kategori = Kategori::whereHas('parent', function ($query) {
            $query->whereRaw('LOWER(nama) = ?', ['kategori produk']);
        })->get();

        return Inertia::render('report/Produk', [
            'breadcrumbs' => $breadcrumbs,
            'kategori' => $kategori
        ]);
    }

    public function jahit()
    {
        $breadcrumbs = [
            ['title' => 'Report', 'href' => route('report.index')],
        ];

        $statusJahit = Kategori::whereHas('parent', function ($query) {
            $query->whereRaw('LOWER(nama) = ?', ['status jahit']);
        })->get();

        return Inertia::render('report/Jahit', [
            'breadcrumbs' => $breadcrumbs,
            'status_jahit_options' => $statusJahit,
        ]);
    }

    public function cuttingKain()
    {
        $breadcrumbs = [
            ['title' => 'Report', 'href' => route('report.index')],
        ];

        $statusCuttingKain = Kategori::whereHas('parent', function ($query) {
            $query->whereRaw('LOWER(nama) = ?', ['status cutting kain']);
        })->get();

        return Inertia::render('report/CuttingKain', [
            'breadcrumbs' => $breadcrumbs,
            'status_cutting_kain_options' => $statusCuttingKain,
        ]);
    }

    public function orderExportPdf(Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');
        $statusOrder = $request->input('status_order');
        $statusPembayaran = $request->input('status_pembayaran');

        $orders = Order::with(['status', 'pembayaran'])
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->when($statusOrder, fn ($q) => $q->where('status_id', $statusOrder))
            ->when($statusPembayaran, fn ($q) => $q->where('status_pembayaran_id', $statusPembayaran))
        ->latest()
        ->get();

        $pdf = Pdf::loadView('exports.orders-pdf', [
            'orders' => $orders,
            'month' => $month,
            'year' => $year,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("laporan_order_{$month}_{$year}.pdf");
    }

    public function orderExportExcel(Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');
        $statusOrder = $request->input('status_order');
        $statusPembayaran = $request->input('status_pembayaran');

        return Excel::download(
            new OrderExport($month, $year, $statusOrder, $statusPembayaran),
            "laporan_order_{$month}_{$year}.xlsx"
        );
    }

    public function kerusakanExportPdf(Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');
        $statusKerusakan = $request->input('status_kerusakan');
        $statusChecking = $request->input('status_checking');

        $laporanKerusakan = LaporanKerusakan::with(['order', 'status', 'statusChecking'])
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->when($statusKerusakan, fn ($q) => $q->where('status_id', $statusKerusakan))
            ->when($statusChecking, fn ($q) => $q->where('status_checking_id', $statusChecking))
        ->latest()
        ->get();

        $pdf = Pdf::loadView('exports.kerusakan-pdf', [
            'laporanKerusakan' => $laporanKerusakan,
            'month' => $month,
            'year' => $year,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("laporan_kerusakan_{$month}_{$year}.pdf");
    }

    public function kerusakanExportExcel(Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');
        $statusKerusakan = $request->input('status_kerusakan');
        $statusChecking = $request->input('status_checking');

        return Excel::download(
            new LaporanKerusakanExport($month, $year, $statusKerusakan, $statusChecking),
            "laporan_kerusakan_{$month}_{$year}.xlsx"
        );
    }

    public function produkExportPdf(Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');
        $kategori = $request->input('kategori');

        // Ambil semua produk
        $produks = Produk::with(['kategori', 'satuan'])
            ->when($kategori, fn($q) => $q->where('kategori_id', $kategori))
            ->get();

        // Ambil jumlah qty dari order_detail
        $orderDetailQty = DB::table('order_detail')
            ->join('order', 'order_detail.order_id', '=', 'order.id_order')
            ->select('order_detail.produk_id', DB::raw('SUM(order_detail.qty) as total_terjual'))
            ->whereMonth('order.created_at', $month)
            ->whereYear('order.created_at', $year)
            ->groupBy('order_detail.produk_id')
            ->pluck('total_terjual', 'order_detail.produk_id');

        // Ambil jumlah qty dari order_tambahan
        $orderTambahanQty = DB::table('order_tambahan')
            ->join('order_detail', 'order_tambahan.order_detail_id', '=', 'order_detail.id')
            ->join('order', 'order_detail.order_id', '=', 'order.id_order')
            ->select('order_tambahan.produk_id', DB::raw('SUM(order_tambahan.qty) as total_terjual'))
            ->whereMonth('order.created_at', $month)
            ->whereYear('order.created_at', $year)
            ->groupBy('order_tambahan.produk_id')
            ->pluck('total_terjual', 'order_tambahan.produk_id');

        // Gabungkan qty ke produk
        foreach ($produks as $produk) {
            $qty1 = $orderDetailQty[$produk->id_produk] ?? 0;
            $qty2 = $orderTambahanQty[$produk->id_produk] ?? 0;
            $produk->total_terjual = $qty1 + $qty2;
        }

        $pdf = Pdf::loadView('exports.produk-pdf', [
            'produks' => $produks,
            'month' => $month,
            'year' => $year,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("laporan_produk_{$month}_{$year}.pdf");
    }

    public function produkExportExcel(Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');
        $kategori = $request->input('kategori');

        return Excel::download(new ProdukExport($month, $year, $kategori), "laporan_produk_{$month}_{$year}.xlsx");
    }

    public function jahitExportPdf(Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');
        $kategori = $request->input('kategori');

        $jahits = RiwayatJahit::query()
            ->select('riwayat_jahit.*')
            ->join('jahit', 'riwayat_jahit.jahit_id', '=', 'jahit.id')
            ->with([
                'jahit.order',
                'jahit.status'
            ])
            ->whereMonth('riwayat_jahit.created_at', $month)
            ->whereYear('riwayat_jahit.created_at', $year)
            ->when($kategori, fn ($q) => $q->where('jahit.status_id', $kategori))
            ->orderBy('jahit.order_id')
            ->get();

        $pdf = Pdf::loadView('exports.jahit-pdf', [
            'jahits' => $jahits,
            'month' => $month,
            'year' => $year,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("laporan_jahit_{$month}_{$year}.pdf");
    }

    public function jahitExportExcel(Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');
        $kategori = $request->input('kategori');

        return Excel::download(new JahitExport($month, $year, $kategori), "laporan_jahit_{$month}_{$year}.xlsx");
    }

    public function cuttingKainExportPdf(Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');
        $kategori = $request->input('kategori');

        $cuttingKains = RiwayatCuttingKain::query()
            ->select('riwayat_cutting_kain.*')
            ->join('cutting_kain', 'riwayat_cutting_kain.cutting_kain_id', '=', 'cutting_kain.id')
            ->with([
                'cuttingKain.order',
                'cuttingKain.status'
            ])
            ->whereMonth('riwayat_cutting_kain.created_at', $month)
            ->whereYear('riwayat_cutting_kain.created_at', $year)
            ->when($kategori, fn ($q) => $q->where('cutting_kain.status_id', $kategori))
            ->orderBy('cutting_kain.order_id')
            ->get();

        $pdf = Pdf::loadView('exports.cutting-kain-pdf', [
            'cuttingKains' => $cuttingKains,
            'month' => $month,
            'year' => $year,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("laporan_cutting_kain_{$month}_{$year}.pdf");
    }

    public function cuttingKainExportExcel(Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');
        $kategori = $request->input('kategori');

        return Excel::download(new CuttingKainExport($month, $year, $kategori), "laporan_cutting_kain_{$month}_{$year}.xlsx");
    }
}
