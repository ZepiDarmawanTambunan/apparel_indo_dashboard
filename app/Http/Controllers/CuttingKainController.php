<?php

namespace App\Http\Controllers;

use App\Models\CuttingKain;
use App\Models\Jahit;
use App\Models\Kategori;
use App\Models\LaporanKerusakan;
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
        $cuttingKain = CuttingKain::with([
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
        $users = User::select('id', 'nama')->get();
        return Inertia::render('cutting-kain/Show', [
            'cutting_kain' => $cuttingKain,
            'users' => $users,
            'laporan_kerusakan' => $laporanKerusakan,
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

    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'jumlah_dikerjakan' => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();

        try {

            $cuttingKain = CuttingKain::findOrFail($id);
            $user = User::findOrFail($request->user_id);

            if ($request->filled('riwayat_cutting_kain_id')) {
                // ✅ 1. Update riwayat
                $riwayat = RiwayatCuttingKain::findOrFail($request->riwayat_cutting_kain_id);

                $riwayat->update([
                    'cutting_kain_id' => $cuttingKain->id,
                    'user_id' => $request->user_id,
                    'user_nama' => $user->nama,
                    'jumlah_dikerjakan' => $request->jumlah_dikerjakan ?? 0,
                ]);

            } else {
                // ✅ 2. Buat riwayat baru
                $riwayat = RiwayatCuttingKain::create([
                    'cutting_kain_id' => $cuttingKain->id,
                    'user_id' => $request->user_id,
                    'user_nama' => $user->nama,
                    'jumlah_dikerjakan' => $request->jumlah_dikerjakan ?? 0,
                ]);
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
