<?php

namespace App\Http\Controllers;

use App\Models\CuttingKain;
use App\Models\Kategori;
use App\Models\LaporanKerusakan;
use App\Models\PressKain;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PressKainController extends Controller
{
    public function index()
    {
        $breadcrumbs = [
            ['title' => 'Press Kain', 'href' => route('press-kain.index')],
        ];

        $pressKain = PressKain::with(['order', 'status'])
        ->whereNull('deleted_at')
        ->orderBy('updated_at', 'DESC')
        ->get();
        return Inertia::render('press-kain/Index', [
            'breadcrumbs' => $breadcrumbs,
            'press_kain' => $pressKain,
        ]);
    }

    public function show($id)
    {
        $pressKain = PressKain::with([
            'order.status',
            'order.statusPembayaran',
            'status',
        ])->findOrFail($id);
        $laporanKerusakan = LaporanKerusakan::with(['status'])
        ->where('order_id', $pressKain->order_id)
        // ->where('divisi_pelapor', 'Press Kain')
        ->whereNull('deleted_at')
        ->orderBy('updated_at', 'DESC')
        ->get();
        $users = User::select('id', 'nama')->get();
        return Inertia::render('press-kain/Show', [
            'press_kain' => $pressKain,
            'laporan_kerusakan' => $laporanKerusakan,
            'users' => $users,
        ]);
    }

    public function terima($id)
    {
        $pressKain = PressKain::findOrFail($id);
        $user = Auth::user();
        $statusId = Kategori::getKategoriId('Status Press Kain', 'Proses');
        $pressKain->update([
            'status_id' => $statusId,
            'tgl_terima' => now(),
            'user_id' => $user->id,
            'user_nama' => $user->nama,
        ]);
        return redirect()->route('press-kain.index')->with('toast', [
            'type' => 'success',
            'message' => 'Order berhasil diterima dan status diupdate.',
        ]);
    }

    public function batal($id)
    {
        DB::beginTransaction();

        try {
            $pressKain = PressKain::with(['order'])->findOrFail($id);
            $order = $pressKain->order;
            $user = Auth::user();
            $statusProsesId = Kategori::getKategoriId('Status Press Kain', 'Proses');
            $statusSelesaiId = Kategori::getKategoriId('Status Press Kain', 'Selesai');

            // if ($order && $order->prosesKerjaTerakhir() != 'Press Kain') {
            //     throw new \Exception('Batalkan terlebih dahulu proses kerja terakhir (' . $order->prosesKerjaTerakhir() . ') sebelum membatalkan Data Press Kain.');
            // }

            if($pressKain->status_id == $statusSelesaiId){
                $pressKain->update([
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

            return redirect()->route('press-kain.index')->with('toast', [
                'type' => 'success',
                'message' => 'Order berhasil dibatalkan dan status diupdate.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal membatalkan data Press Kain: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'keterangan' => 'nullable|string',
            'file_press_kain' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        DB::beginTransaction();

        try {
            $pressKain = PressKain::findOrFail($id);

            // Update PressKain
            $pressKain->update([
                'user_id' => $request->user_id,
                'user_nama' => $pressKain->user?->nama,
                'keterangan' => $request->keterangan,
            ]);

            // Upload file
            if ($request->hasFile('file_press_kain')) {
                $pressKain
                    ->addMediaFromRequest('file_press_kain')
                    ->usingName('file_press_kain')
                    ->toMediaCollection('file_press_kain');
            }

            DB::commit();

            return redirect()->route('press-kain.show', $pressKain->id)->with('toast', [
                'type' => 'success',
                'message' => 'Data berhasil diperbarui.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors(['error' => 'Gagal membuat press kain: ' . $e->getMessage()]);
        }
    }

    public function selesai($id)
    {

        DB::beginTransaction();

        try {
            $pressKain = PressKain::with([
                'order',
            ])->findOrFail($id);
            $order = $pressKain->order;
            $user = Auth::user();
            $statusId = Kategori::getKategoriId('Status Press Kain', 'Selesai');

            $pressKain->update([
                'status_id' => $statusId,
                'tgl_selesai' => now(),
                'user_id' => $user->id,
                'user_nama' => $user->nama,
            ]);

            $cuttingKainExists = CuttingKain::where('order_id', $order->id_order)->exists();
            if (!$cuttingKainExists) {
                $statusCuttingKainId = Kategori::getKategoriId('Status Cutting Kain', 'Belum Diterima');
                CuttingKain::create([
                    'order_id' => $order->id_order,
                    'status_id' => $statusCuttingKainId,
                ]);
            }

            $statusOrderId = $order->statusOrderTerakhir();
            if($statusOrderId){
                $order->update(['status_id' => $statusOrderId]);
            }

            DB::commit();

            return redirect()->route('press-kain.index')->with('toast', [
                'type' => 'success',
                'message' => 'Press kain berhasil ditandai selesai.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            dd($e);
            report($e);
            return back()->withErrors(['error' => 'Gagal menyelesaikan press kain: ' . $e->getMessage()]);
        }
    }
}
