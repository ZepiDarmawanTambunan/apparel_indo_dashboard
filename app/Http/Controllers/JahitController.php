<?php

namespace App\Http\Controllers;

use App\Models\Jahit;
use App\Models\Kategori;
use App\Models\LaporanKerusakan;
use App\Models\RiwayatJahit;
use App\Models\SablonPress;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JahitController extends Controller
{
    public function index()
    {
        $breadcrumbs = [
            ['title' => 'Jahit', 'href' => route('jahit.index')],
        ];

        $jahit = Jahit::with(['order', 'status'])
        ->whereNull('deleted_at')
        ->orderBy('updated_at', 'DESC')
        ->get();
        return Inertia::render('jahit/Index', [
            'breadcrumbs' => $breadcrumbs,
            'jahit' => $jahit,
        ]);
    }

    public function show($id)
    {
        $jahit = Jahit::with([
            'order.status',
            'order.statusPembayaran',
            'status',
            'riwayatJahit'
        ])->findOrFail($id);
        $laporanKerusakan = LaporanKerusakan::with(['status'])
        ->where('order_id', $jahit->order_id)
        // ->where('divisi_pelapor', 'Jahit')
        ->whereNull('deleted_at')
        ->orderBy('updated_at', 'DESC')
        ->get();
        $users = User::select('id', 'nama')->get();
        return Inertia::render('jahit/Show', [
            'jahit' => $jahit,
            'users' => $users,
            'laporan_kerusakan' => $laporanKerusakan,
        ]);
    }

    public function terima($id)
    {
        $jahit = Jahit::findOrFail($id);
        $user = Auth::user();
        $statusId = Kategori::getKategoriId('Status Jahit', 'Proses');
        $jahit->update([
            'status_id' => $statusId,
            'tgl_terima' => now(),
            'user_id' => $user->id,
            'user_nama' => $user->nama,
        ]);
        return redirect()->route('jahit.index')->with('toast', [
            'type' => 'success',
            'message' => 'Order berhasil diterima dan status diupdate.',
        ]);
    }

    public function batal($id)
    {
        DB::beginTransaction();

        try {
            $jahit = Jahit::with(['order'])->findOrFail($id);
            $order = $jahit->order;
            $statusBelumDiterimaId = Kategori::getKategoriId('Status Jahit', 'Belum Diterima');
            $statusProsesId = Kategori::getKategoriId('Status Jahit', 'Proses');
            $statusSelesaiId = Kategori::getKategoriId('Status Jahit', 'Selesai');

            // if ($order && $order->prosesKerjaTerakhir() != 'Jahit') {
            //     throw new \Exception('Batalkan terlebih dahulu proses kerja terakhir (' . $order->prosesKerjaTerakhir() . ') sebelum membatalkan Jahit.');
            // }

            if ($jahit->status_id == $statusProsesId) {
                $jahit->update([
                    'status_id' => $statusBelumDiterimaId,
                    'tgl_batal' => now(),
                ]);
            } elseif ($jahit->status_id == $statusSelesaiId) {
                $jahit->update([
                    'status_id' => $statusProsesId,
                    'tgl_batal' => now(),
                ]);
            }

            $statusOrderId = $order->statusOrderTerakhir();
            if($statusOrderId){
                $order->update(['status_id' => $statusOrderId]);
            }

            DB::commit();

            return redirect()->route('jahit.index')->with('toast', [
                'type' => 'success',
                'message' => 'Order berhasil dibatalkan dan status diupdate.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal membatalkan jahit: ' . $e->getMessage()]);
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

            $jahit = Jahit::findOrFail($id);
            $user = User::findOrFail($request->user_id);

            if ($request->filled('riwayat_jahit_id')) {
                // ✅ 1. Update riwayat
                $riwayat = RiwayatJahit::findOrFail($request->riwayat_jahit_id);

                $riwayat->update([
                    'jahit_id' => $jahit->id,
                    'user_id' => $request->user_id,
                    'user_nama' => $user->nama,
                    'jumlah_dikerjakan' => $request->jumlah_dikerjakan ?? 0,
                ]);

            } else {
                // ✅ 2. Buat riwayat baru
                $riwayat = RiwayatJahit::create([
                    'jahit_id' => $jahit->id,
                    'user_id' => $request->user_id,
                    'user_nama' => $user->nama,
                    'jumlah_dikerjakan' => $request->jumlah_dikerjakan ?? 0,
                ]);
            }

            DB::commit();

            return redirect()->route('jahit.show', $jahit->id)->with('toast', [
                'type' => 'success',
                'message' => 'Data berhasil diperbarui.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal membuat jahit: ' . $e->getMessage()]);
        }
    }

    public function selesai($id)
    {
        DB::beginTransaction();

        try {
            $jahit = Jahit::with([
                'order',
            ])->findOrFail($id);
            $order = $jahit->order;
            $statusId = Kategori::getKategoriId('Status jahit', 'Selesai');

            $jahit->update([
                'status_id' => $statusId,
                'tgl_selesai' => now(),
            ]);

            $sablonPressExists = SablonPress::where('order_id', $order->id_order)->exists();
            if (!$sablonPressExists) {
                $statusCetakId = Kategori::getKategoriId('Status Sablon Press', 'Proses');
                SablonPress::create([
                    'order_id' => $order->id_order,
                    'status_id' => $statusCetakId,
                ]);
            }

            $statusOrderId = $order->statusOrderTerakhir();
            if($statusOrderId){
                $order->update(['status_id' => $statusOrderId]);
            }

            DB::commit();

            return redirect()->route('jahit.index')->with('toast', [
                'type' => 'success',
                'message' => 'Jahit berhasil ditandai selesai.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors(['error' => 'Gagal menyelesaikan jahit: ' . $e->getMessage()]);
        }
    }
}
