<?php

namespace App\Http\Controllers;

use App\Models\Packaging;
use App\Models\Kategori;
use App\Models\LaporanKerusakan;
use App\Models\User;
use App\Models\QC;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class PackagingController extends Controller
{
    public function index()
    {
        $breadcrumbs = [
            ['title' => 'Packaging', 'href' => route('packaging.index')],
        ];

        $packaging = Packaging::with(['order', 'status'])
        ->whereNull('deleted_at')
        ->orderBy('updated_at', 'DESC')
        ->get();
        return Inertia::render('packaging/Index', [
            'breadcrumbs' => $breadcrumbs,
            'packaging' => $packaging,
        ]);
    }

    public function show($id)
    {
        $packaging = Packaging::with([
            'order.status',
            'order.statusPembayaran',
            'status',
        ])->findOrFail($id);
        $laporanKerusakan = LaporanKerusakan::with(['status'])
        ->where('order_id', $packaging->order_id)
        // ->where('divisi_pelapor', 'Packaging')
        ->whereNull('deleted_at')
        ->orderBy('updated_at', 'DESC')
        ->get();
        $users = User::select('id', 'nama')->get();
        return Inertia::render('packaging/Show', [
            'packaging' => $packaging,
            'laporan_kerusakan' => $laporanKerusakan,
            'users' => $users,
        ]);
    }

    public function terima($id)
    {
        $packaging = Packaging::findOrFail($id);
        $user = Auth::user();
        $statusId = Kategori::getKategoriId('Status Packaging', 'Proses');
        $packaging->update([
            'status_id' => $statusId,
            'tgl_terima' => now(),
            'user_id' => $user->id,
            'user_nama' => $user->nama,
        ]);
        return redirect()->route('packaging.index')->with('toast', [
            'type' => 'success',
            'message' => 'Order berhasil diterima dan status diupdate.',
        ]);
    }

    public function batal($id)
    {
        DB::beginTransaction();

        try {
            $packaging = Packaging::with(['order'])->findOrFail($id);
            $order = $packaging->order;
            $user = Auth::user();
            $statusProsesId = Kategori::getKategoriId('Status Packaging', 'Proses');
            $statusSelesaiId = Kategori::getKategoriId('Status Packaging', 'Selesai');

            // if ($order && $order->prosesKerjaTerakhir() != 'Packaging') {
            //     throw new \Exception('Batalkan terlebih dahulu proses kerja terakhir (' . $order->prosesKerjaTerakhir() . ') sebelum membatalkan packaging.');
            // }

            if($packaging->status_id == $statusSelesaiId){
                $packaging->update([
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

            return redirect()->route('packaging.index')->with('toast', [
                'type' => 'success',
                'message' => 'Order berhasil dibatalkan dan status diupdate.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal membatalkan data packaging: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'keterangan' => 'nullable|string',
            'file_packaging' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        DB::beginTransaction();

        try {
            $packaging = Packaging::findOrFail($id);

            // Update PressKain
            $packaging->update([
                'user_id' => $request->user_id,
                'user_nama' => $packaging->user?->nama,
                'keterangan' => $request->keterangan,
            ]);

            // Upload file
            if ($request->hasFile('file_packaging')) {
                $packaging
                    ->addMediaFromRequest('file_packaging')
                    ->usingName('file_packaging')
                    ->toMediaCollection('file_packaging');
            }

            DB::commit();

            return redirect()->route('packaging.show', $packaging->id)->with('toast', [
                'type' => 'success',
                'message' => 'Data berhasil diperbarui.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors(['error' => 'Gagal membuat packaging: ' . $e->getMessage()]);
        }
    }

    public function selesai($id)
    {

        DB::beginTransaction();

        try {
            $packaging = Packaging::with([
                'order',
            ])->findOrFail($id);
            $order = $packaging->order;
            $user = Auth::user();
            $statusId = Kategori::getKategoriId('Status Packaging', 'Selesai');

            $packaging->update([
                'status_id' => $statusId,
                'tgl_selesai' => now(),
                'user_id' => $user->id,
                'user_nama' => $user->nama,
            ]);

            $statusOrderId = $order->statusOrderTerakhir();
            if($statusOrderId){
                $order->update(['status_id' => $statusOrderId]);
            }

            DB::commit();

            return redirect()->route('packaging.index')->with('toast', [
                'type' => 'success',
                'message' => 'Packaging berhasil ditandai selesai.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            dd($e);
            report($e);
            return back()->withErrors(['error' => 'Gagal menyelesaikan packaging: ' . $e->getMessage()]);
        }
    }
}
