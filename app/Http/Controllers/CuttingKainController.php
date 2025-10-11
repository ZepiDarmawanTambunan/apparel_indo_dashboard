<?php

namespace App\Http\Controllers;

use App\Models\CuttingKain;
use App\Models\Jahit;
use App\Models\Kategori;
use App\Models\LaporanKerusakan;
use App\Models\Produk;
use App\Models\RiwayatCuttingKain;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CuttingKainController extends Controller
{
    public function index()
    {
        $breadcrumbs = [
            ['title' => 'Menu', 'href' => route('dashboard')],
            ['title' => 'Cutting Kain', 'href' => route('cutting-kain.index')],
        ];

        $cuttingKain = CuttingKain::with(['order', 'status'])
            ->whereNull('deleted_at')
            ->orderBy('updated_at', 'DESC')
            ->get();
        return Inertia::render('cutting-kain/Index', [
            'breadcrumbs' => $breadcrumbs,
            'cutting_kain' => $cuttingKain,
        ]);
    }

    public function show($id)
    {
        $breadcrumbs = [
            ['title' => 'Menu', 'href' => route('dashboard')],
            ['title' => 'Cutting Kain', 'href' => route('cutting-kain.index')],
            ['title' => 'Detil', 'href' => route('cutting-kain.show', $id)],
        ];

        $cuttingKain = CuttingKain::with([
            'order.orderDetail.produk.salaries',
            'order.orderDetail.orderTambahan.produk.salaries',
            'order.status',
            'order.statusPembayaran',
            'status',
            'riwayatCuttingKain'
        ])->findOrFail($id);

        $laporanKerusakan = LaporanKerusakan::with(['status'])
            ->where('order_id', $cuttingKain->order_id)
            // ->where('divisi_pelapor', 'Cutting Kain')
            ->whereNull('deleted_at')
            ->orderBy('updated_at', 'DESC')
            ->get();

        $order = $cuttingKain->order;
        if (!$order) {
            $produks = collect();
        } else {
            $produkFromOrderDetail = $order->orderDetail->map(fn ($detail) => [
                'id_produk' => $detail->produk->id_produk,
                'nama'      => $detail->produk->nama,
                'qty'       => $detail->qty,
                'salaries'  => $detail->produk->salaries,
            ]);

            $produkFromOrderTambahan = $order->orderDetail
                ->flatMap->orderTambahan
                ->map(fn ($tambahan) => [
                    'id_produk' => $tambahan->produk_id,
                    'nama'      => $tambahan->nama,
                    'qty'       => $tambahan->qty,
                    'salaries'  => $tambahan->produk->salaries,
                ]);

            $allProduk = $produkFromOrderDetail->merge($produkFromOrderTambahan);
            $produks = $allProduk->groupBy(fn ($item) => $item['id_produk'].'|'.$item['nama'])
                ->map(fn ($items) => [
                    'id_produk' => $items->first()['id_produk'],
                    'nama'      => $items->first()['nama'],
                    'qty'       => $items->sum('qty'),
                    'salaries'  => $items->first()['salaries'],
                ])
                ->values();
        }

        $users = User::select('id', 'nama')->get();
        return Inertia::render('cutting-kain/Show', [
            'cutting_kain' => $cuttingKain,
            'users' => $users,
            'produks' => $produks,
            'laporan_kerusakan' => $laporanKerusakan,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    public function terima($id)
    {
        $cuttingKain = CuttingKain::findOrFail($id);
        $user = Auth::user();
        $statusId = Kategori::getKategoriId('Status Cutting Kain', 'Proses');
        $cuttingKain->update([
            'status_id' => $statusId,
            'tgl_terima' => now(),
            'user_id' => $user->id,
            'user_nama' => $user->nama,
        ]);
        return redirect()->route('cutting-kain.index')->with('toast', [
            'type' => 'success',
            'message' => 'Order berhasil diterima dan status diupdate.',
        ]);
    }

    public function batal($id)
    {
        DB::beginTransaction();

        try {
            $cuttingKain = CuttingKain::with(['order'])->findOrFail($id);
            $order = $cuttingKain->order;
            $statusBelumDiterimaId = Kategori::getKategoriId('Status Cutting Kain', 'Belum Diterima');
            $statusProsesId = Kategori::getKategoriId('Status Cutting Kain', 'Proses');
            $statusSelesaiId = Kategori::getKategoriId('Status Cutting Kain', 'Selesai');

            // if ($order && $order->prosesKerjaTerakhir() != 'Cutting Kain') {
            //     throw new \Exception('Batalkan terlebih dahulu proses kerja terakhir (' . $order->prosesKerjaTerakhir() . ') sebelum membatalkan Cutting Kain.');
            // }

            if ($cuttingKain->status_id == $statusProsesId) {
                $cuttingKain->update([
                    'status_id' => $statusBelumDiterimaId,
                    'tgl_batal' => now(),
                ]);
            } elseif ($cuttingKain->status_id == $statusSelesaiId) {
                $cuttingKain->update([
                    'status_id' => $statusProsesId,
                    'tgl_batal' => now(),
                ]);
            }

            $statusOrderId = $order->statusOrderTerakhir();
            if($statusOrderId){
                $order->update(['status_id' => $statusOrderId]);
            }

            DB::commit();

            return redirect()->route('cutting-kain.index')->with('toast', [
                'type' => 'success',
                'message' => 'Order berhasil dibatalkan dan status diupdate.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal membatalkan cutting kain: ' . $e->getMessage()]);
        }
    }

    public function batalRiwayat($id)
    {
        DB::beginTransaction();

        try {
            $riwayatCuttingKain = RiwayatCuttingKain::findOrFail($id);

            $riwayatCuttingKain->delete();
            DB::commit();

            return back()->with('toast', [
                'type' => 'success',
                'message' => 'Riwayat berhasil dibatalkan dan status diupdate.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal membatalkan riwayat: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'jumlah_dikerjakan' => 'required|integer|min:1',
            'produk_id' => 'required|exists:produk,id_produk',
            'salary' => 'required|numeric|min:1000',
        ]);

        DB::beginTransaction();

        try {

            $cuttingKain = CuttingKain::findOrFail($id);
            $user = User::findOrFail($request->user_id);
            // Ambil nama produk (jika ada)
            $produkNama = null;
            if ($request->filled('produk_id')) {
                $produk = Produk::where('id_produk', $request->produk_id)->first();
                $produkNama = $produk?->nama;
            }

            $dataRiwayat = [
                'cutting_kain_id'   => $cuttingKain->id,
                'user_id'           => $request->user_id,
                'user_nama'         => $user->nama,
                'produk_id'         => $request->produk_id,
                'produk_nama'       => $produkNama,
                'salary'            => $request->salary ?? 0,
                'jumlah_dikerjakan' => $request->jumlah_dikerjakan ?? 0,
            ];

            if ($request->filled('riwayat_cutting_kain_id')) {
                $riwayat = RiwayatCuttingKain::findOrFail($request->riwayat_cutting_kain_id);
                $riwayat->update($dataRiwayat);
            } else {
                $riwayat = RiwayatCuttingKain::create($dataRiwayat);
            }

            DB::commit();

            return redirect()->route('cutting-kain.show', $cuttingKain->id)->with('toast', [
                'type' => 'success',
                'message' => 'Data berhasil diperbarui.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal membuat cutting kain: ' . $e->getMessage()]);
        }
    }

    public function selesai($id)
    {
        DB::beginTransaction();

        try {
            $cuttingKain = CuttingKain::with([
                'order',
            ])->findOrFail($id);
            $order = $cuttingKain->order;
            $statusId = Kategori::getKategoriId('Status Cutting Kain', 'Selesai');

            $cuttingKain->update([
                'status_id' => $statusId,
                'tgl_selesai' => now(),
            ]);

            $jahitExists = Jahit::where('order_id', $order->id_order)->exists();
            if (!$jahitExists) {
                $statusCetakId = Kategori::getKategoriId('Status Jahit', 'Belum Diterima');
                Jahit::create([
                    'order_id' => $order->id_order,
                    'status_id' => $statusCetakId,
                ]);
            }

            $statusOrderId = $order->statusOrderTerakhir();
            if($statusOrderId){
                $order->update(['status_id' => $statusOrderId]);
            }

            DB::commit();

            return redirect()->route('cutting-kain.index')->with('toast', [
                'type' => 'success',
                'message' => 'Cutting kain berhasil ditandai selesai.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors(['error' => 'Gagal menyelesaikan cutting kain: ' . $e->getMessage()]);
        }
    }
}
