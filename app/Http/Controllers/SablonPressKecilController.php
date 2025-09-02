<?php

namespace App\Http\Controllers;

use App\Models\SablonPress;
use App\Models\Kategori;
use App\Models\LaporanKerusakan;
use App\Models\User;
use App\Models\QC;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class SablonPressKecilController extends Controller
{
    public function index()
    {
        $breadcrumbs = [
            ['title' => 'Sablon Press Kecil', 'href' => route('sablon-press-kecil.index')],
        ];

        $sablonPress = SablonPress::with(['order', 'status'])
        ->whereNull('deleted_at')
        ->orderBy('updated_at', 'DESC')
        ->get();
        return Inertia::render('sablon-press-kecil/Index', [
            'breadcrumbs' => $breadcrumbs,
            'sablon_press' => $sablonPress,
        ]);
    }

    public function show($id)
    {
        $sablonPress = SablonPress::with([
            'order.status',
            'order.statusPembayaran',
            'status',
        ])->findOrFail($id);
        $laporanKerusakan = LaporanKerusakan::with(['status'])
        ->where('order_id', $sablonPress->order_id)
        // ->where('divisi_pelapor', 'Sablon Press')
        ->whereNull('deleted_at')
        ->orderBy('updated_at', 'DESC')
        ->get();
        $users = User::select('id', 'nama')->get();
        return Inertia::render('sablon-press-kecil/Show', [
            'sablon_press' => $sablonPress,
            'laporan_kerusakan' => $laporanKerusakan,
            'users' => $users,
        ]);
    }

    public function terima($id)
    {
        $sablonPress = SablonPress::findOrFail($id);
        $user = Auth::user();
        $statusId = Kategori::getKategoriId('Status Sablon Press', 'Proses');
        $sablonPress->update([
            'status_id' => $statusId,
            'tgl_terima' => now(),
            'user_id' => $user->id,
            'user_nama' => $user->nama,
        ]);
        return redirect()->route('sablon-press-kecil.index')->with('toast', [
            'type' => 'success',
            'message' => 'Order berhasil diterima dan status diupdate.',
        ]);
    }

    public function batal($id)
    {
        DB::beginTransaction();

        try {
            $sablonPress = SablonPress::with(['order'])->findOrFail($id);
            $order = $sablonPress->order;
            $user = Auth::user();
            $statusProsesId = Kategori::getKategoriId('Status Sablon Press', 'Proses');
            $statusSelesaiId = Kategori::getKategoriId('Status Sablon Press', 'Selesai');

            // if ($order && $order->prosesKerjaTerakhir() != 'Sablon Press') {
            //     throw new \Exception('Batalkan terlebih dahulu proses kerja terakhir (' . $order->prosesKerjaTerakhir() . ') sebelum membatalkan Data Sablon Press.');
            // }

            if($sablonPress->status_id == $statusSelesaiId){
                $sablonPress->update([
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

            return redirect()->route('sablon-press-kecil.index')->with('toast', [
                'type' => 'success',
                'message' => 'Order berhasil dibatalkan dan status diupdate.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal membatalkan data sablon press: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'keterangan' => 'nullable|string',
            'file_sablon_press' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        DB::beginTransaction();

        try {
            $sablonPress = SablonPress::findOrFail($id);

            // Update PressKain
            $sablonPress->update([
                'user_id' => $request->user_id,
                'user_nama' => $sablonPress->user?->nama,
                'keterangan' => $request->keterangan,
            ]);

            // Upload file
            if ($request->hasFile('file_sablon_press')) {
                $sablonPress
                    ->addMediaFromRequest('file_sablon_press')
                    ->usingName('file_sablon_press')
                    ->toMediaCollection('file_sablon_press');
            }

            DB::commit();

            return redirect()->route('sablon-press-kecil.show', $sablonPress->id)->with('toast', [
                'type' => 'success',
                'message' => 'Data berhasil diperbarui.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors(['error' => 'Gagal membuat sablon press: ' . $e->getMessage()]);
        }
    }

    public function selesai($id)
    {

        DB::beginTransaction();

        try {
            $sablonPress = SablonPress::with([
                'order',
            ])->findOrFail($id);
            $order = $sablonPress->order;
            $user = Auth::user();
            $statusId = Kategori::getKategoriId('Status Sablon Press', 'Selesai');

            $sablonPress->update([
                'status_id' => $statusId,
                'tgl_selesai' => now(),
                'user_id' => $user->id,
                'user_nama' => $user->nama,
            ]);

            $qcExists = QC::where('order_id', $order->id_order)->exists();
            if (!$qcExists) {
                $statusQcId = Kategori::getKategoriId('Status QC', 'Proses');
                QC::create([
                    'order_id' => $order->id_order,
                    'status_id' => $statusQcId,
                ]);
            }

            $statusOrderId = $order->statusOrderTerakhir();
            if($statusOrderId){
                $order->update(['status_id' => $statusOrderId]);
            }

            DB::commit();

            return redirect()->route('sablon-press-kecil.index')->with('toast', [
                'type' => 'success',
                'message' => 'Sablon Press berhasil ditandai selesai.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            dd($e);
            report($e);
            return back()->withErrors(['error' => 'Gagal menyelesaikan sablon press: ' . $e->getMessage()]);
        }
    }
}
