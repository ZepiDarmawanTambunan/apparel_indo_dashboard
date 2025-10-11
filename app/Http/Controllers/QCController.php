<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Kategori;
use App\Models\LaporanKerusakan;
use App\Models\Packaging;
use App\Models\User;
use App\Models\QC;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class QCController extends Controller
{
    public function index()
    {
        $breadcrumbs = [
            ['title' => 'Menu', 'href' => route('dashboard')],
            ['title' => 'QC', 'href' => route('qc.index')],
        ];

        $qc = QC::with(['order', 'status'])
        ->whereNull('deleted_at')
        ->orderBy('updated_at', 'DESC')
        ->get();
        return Inertia::render('qc/Index', [
            'breadcrumbs' => $breadcrumbs,
            'qc' => $qc,
        ]);
    }

    public function show($id)
    {
        $breadcrumbs = [
            ['title' => 'Menu', 'href' => route('dashboard')],
            ['title' => 'QC', 'href' => route('qc.index')],
            ['title' => 'Detil', 'href' => route('qc.show', $id)],
        ];

        $qc = QC::with([
            'order.status',
            'order.statusPembayaran',
            'status',
        ])->findOrFail($id);
        $laporanKerusakan = LaporanKerusakan::with(['status'])
        ->where('order_id', $qc->order_id)
        // ->where('divisi_pelapor', 'QC')
        ->whereNull('deleted_at')
        ->orderBy('updated_at', 'DESC')
        ->get();
        $users = User::select('id', 'nama')->get();
        return Inertia::render('qc/Show', [
            'qc' => $qc,
            'laporan_kerusakan' => $laporanKerusakan,
            'users' => $users,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    public function terima($id)
    {
        $qc = QC::findOrFail($id);
        $user = Auth::user();
        $statusId = Kategori::getKategoriId('Status QC', 'Proses');
        $qc->update([
            'status_id' => $statusId,
            'tgl_terima' => now(),
            'user_id' => $user->id,
            'user_nama' => $user->nama,
        ]);
        return redirect()->route('qc.index')->with('toast', [
            'type' => 'success',
            'message' => 'Order berhasil diterima dan status diupdate.',
        ]);
    }

    public function batal($id)
    {
        DB::beginTransaction();

        try {
            $qc = QC::with(['order'])->findOrFail($id);
            $order = $qc->order;
            $user = Auth::user();
            $statusProsesId = Kategori::getKategoriId('Status QC', 'Proses');
            $statusSelesaiId = Kategori::getKategoriId('Status QC', 'Selesai');

            // if ($order && $order->prosesKerjaTerakhir() != 'Quality Control') {
            //     throw new \Exception('Batalkan terlebih dahulu proses kerja terakhir (' . $order->prosesKerjaTerakhir() . ') sebelum membatalkan Data quality control.');
            // }

            if($qc->status_id == $statusSelesaiId){
                $qc->update([
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

            return redirect()->route('qc.index')->with('toast', [
                'type' => 'success',
                'message' => 'Order berhasil dibatalkan dan status diupdate.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal membatalkan data quality control: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'keterangan' => 'nullable|string',
            'file_qc' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        DB::beginTransaction();

        try {
            $qc = QC::findOrFail($id);

            // Update PressKain
            $qc->update([
                'user_id' => $request->user_id,
                'user_nama' => $qc->user?->nama,
                'keterangan' => $request->keterangan,
            ]);

            // Upload file
            if ($request->hasFile('file_qc')) {
                $qc
                    ->addMediaFromRequest('file_qc')
                    ->usingName('file_qc')
                    ->toMediaCollection('file_qc');
            }

            DB::commit();

            return redirect()->route('qc.show', $qc->id)->with('toast', [
                'type' => 'success',
                'message' => 'Data berhasil diperbarui.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors(['error' => 'Gagal membuat quality control: ' . $e->getMessage()]);
        }
    }

    public function selesai($id)
    {

        DB::beginTransaction();

        try {
            $qc = QC::with([
                'order',
            ])->findOrFail($id);
            $order = $qc->order;
            $user = Auth::user();
            $statusId = Kategori::getKategoriId('Status QC', 'Selesai');

            $qc->update([
                'status_id' => $statusId,
                'tgl_selesai' => now(),
                'user_id' => $user->id,
                'user_nama' => $user->nama,
            ]);

            $packagingExists = Packaging::where('order_id', $order->id_order)->exists();
            if (!$packagingExists) {
                $statusPackagingId = Kategori::getKategoriId('Status Packaging', 'Proses');
                Packaging::create([
                    'order_id' => $order->id_order,
                    'status_id' => $statusPackagingId,
                ]);
            }

            $statusOrderId = $order->statusOrderTerakhir();
            if($statusOrderId){
                $order->update(['status_id' => $statusOrderId]);
            }

            DB::commit();

            return redirect()->route('qc.index')->with('toast', [
                'type' => 'success',
                'message' => 'quality control berhasil ditandai selesai.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            dd($e);
            report($e);
            return back()->withErrors(['error' => 'Gagal menyelesaikan quality control: ' . $e->getMessage()]);
        }
    }
}
