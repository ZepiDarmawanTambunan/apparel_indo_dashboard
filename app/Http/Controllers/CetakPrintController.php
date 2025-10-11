<?php

namespace App\Http\Controllers;

use App\Models\CetakPrint;
use App\Models\Kategori;
use App\Models\LaporanKerusakan;
use App\Models\PressKain;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CetakPrintController extends Controller
{
    public function index()
    {
        $breadcrumbs = [
            ['title' => 'Menu', 'href' => route('dashboard')],
            ['title' => 'Cetak & Print', 'href' => route('cetak-print.index')],
        ];

        $cetakPrint = CetakPrint::with(['order', 'status'])
        ->whereNull('deleted_at')
        ->orderBy('updated_at', 'DESC')
        ->get();
        return Inertia::render('cetak-print/Index', [
            'breadcrumbs' => $breadcrumbs,
            'cetak_print' => $cetakPrint,
        ]);
    }

    public function show($id)
    {
        $breadcrumbs = [
            ['title' => 'Menu', 'href' => route('dashboard')],
            ['title' => 'Cetak & Print', 'href' => route('cetak-print.index')],
            ['title' => 'Detil', 'href' => route('cetak-print.show', $id)],
        ];
        $cetakPrint = CetakPrint::with([
            'order.status',
            'order.statusPembayaran',
            'status',
        ])->findOrFail($id);
        $laporanKerusakan = LaporanKerusakan::with(['status'])
        ->where('order_id', $cetakPrint->order_id)
        ->whereNull('deleted_at')
        ->get();
        $users = User::select('id', 'nama')->get();
        return Inertia::render('cetak-print/Show', [
            'cetak_print' => $cetakPrint,
            'laporan_kerusakan' => $laporanKerusakan,
            'users' => $users,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    public function terima($id)
    {
        $cetakPrint = CetakPrint::findOrFail($id);
        $user = Auth::user();
        $statusId = Kategori::getKategoriId('Status Cetak Print', 'Proses');
        $cetakPrint->update([
            'status_id' => $statusId,
            'tgl_terima' => now(),
            'user_id' => $user->id,
            'user_nama' => $user->nama,
        ]);
        return redirect()->route('cetak-print.index')->with('toast', [
            'type' => 'success',
            'message' => 'Order berhasil diterima dan status diupdate.',
        ]);
    }

    public function batal($id)
    {
        DB::beginTransaction();

        try {
            $cetakPrint = CetakPrint::with(['order'])->findOrFail($id);
            $order = $cetakPrint->order;
            $user = Auth::user();
            $statusProsesId = Kategori::getKategoriId('Status Cetak Print', 'Proses');
            $statusSelesaiId = Kategori::getKategoriId('Status Cetak Print', 'Selesai');

            // if ($order && $order->prosesKerjaTerakhir() != 'Cetak Print') {
            //     throw new \Exception('Batalkan terlebih dahulu proses kerja terakhir (' . $order->prosesKerjaTerakhir() . ') sebelum membatalkan Data Cetak.');
            // }

            if($cetakPrint->status_id == $statusSelesaiId){
                $cetakPrint->update([
                    'status_id' => $statusProsesId,
                    'tgl_batal' => now(),
                    'user_id' => $user->id,
                    'user_nama' => $user->nama,
                ]);
            }

            $statusOrderId = $order->statusOrderTerakhir();
            if($statusOrderId){
                $order->update(['status_id' => $statusOrderId]);
            }

            DB::commit();

            return redirect()->route('cetak-print.index')->with('toast', [
                'type' => 'success',
                'message' => 'Order berhasil dibatalkan dan status diupdate.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal membatalkan data desain: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'keterangan' => 'nullable|string',
            'file_cetak_print' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:5120',
        ]);
        DB::beginTransaction();

        try {
            $cetakPrint = CetakPrint::findOrFail($id);

            // Update CetakPrint
            $cetakPrint->update([
                'user_id' => $request->user_id,
                'user_nama' => $cetakPrint->user?->nama,
                'keterangan' => $request->keterangan,
            ]);

            // Upload file
            if ($request->hasFile('file_cetak_print')) {
                $cetakPrint
                    ->addMediaFromRequest('file_cetak_print')
                    ->usingName('file_cetak_print')
                    ->toMediaCollection('file_cetak_print');
            }

            DB::commit();

            return redirect()->route('cetak-print.show', $cetakPrint->id)->with('toast', [
                'type' => 'success',
                'message' => 'Data berhasil diperbarui.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors(['error' => 'Gagal membuat cetak print: ' . $e->getMessage()]);
        }
    }

    public function selesai($id)
    {
        DB::beginTransaction();

        try {
            $cetakPrint = CetakPrint::with([
                'order',
            ])->findOrFail($id);
            $order = $cetakPrint->order;
            $user = Auth::user();
            $statusId = Kategori::getKategoriId('Status Cetak Print', 'Selesai');

            $cetakPrint->update([
                'status_id' => $statusId,
                'tgl_selesai' => now(),
                'user_id' => $user->id,
                'user_nama' => $user->nama,
            ]);

            $pressKainExists = PressKain::where('order_id', $order->id_order)->exists();
            if (!$pressKainExists) {
                $statusPressKainId = Kategori::getKategoriId('Status Press Kain', 'Proses');
                PressKain::create([
                    'order_id' => $order->id_order,
                    'status_id' => $statusPressKainId,
                ]);
            }

            $statusOrderId = $order->statusOrderTerakhir();
            if($statusOrderId){
                $order->update(['status_id' => $statusOrderId]);
            }

            DB::commit();

            return redirect()->route('cetak-print.index')->with('toast', [
                'type' => 'success',
                'message' => 'Cetak print berhasil ditandai selesai.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            dd($e);
            report($e);
            return back()->withErrors(['error' => 'Gagal menyelesaikan cetak print: ' . $e->getMessage()]);
        }
    }
}
