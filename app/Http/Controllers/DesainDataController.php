<?php

namespace App\Http\Controllers;

use App\Models\CetakPrint;
use App\Models\DataDesain;
use App\Models\Kategori;
use App\Models\RiwayatDataDesain;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DesainDataController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $breadcrumbs = [
            ['title' => 'Menu', 'href' => route('dashboard')],
            ['title' => 'Desain Data', 'href' => route('desain-data.index')],
        ];

        $query = DataDesain::with(['order', 'status'])
            ->whereNull('deleted_at');

        if (!Auth::user()->hasRole('Super Admin')) {

            $query->where(function ($q) {
                $q->where('accepted_by_id', Auth::id())
                ->orWhereNull('accepted_by_id');
            });
        }

        $dataDesain = $query
            ->orderBy('updated_at', 'DESC')
            ->get();

        return Inertia::render('desain-data/Index', [
            'breadcrumbs' => $breadcrumbs,
            'data_desain' => $dataDesain,
        ]);
    }

    public function show($id)
    {
        $breadcrumbs = [
            ['title' => 'Menu', 'href' => route('dashboard')],
            ['title' => 'Desain Data', 'href' => route('desain-data.index')],
            ['title' => 'Detil', 'href' => route('desain-data.show', $id)],
        ];

        $dataDesain = DataDesain::with([
            'order.status',
            'order.statusPembayaran',
            'status',
            'riwayatDataDesain'
        ])->findOrFail($id);

        $this->authorize('view', $dataDesain);

        $users = User::select('id', 'nama')->get();

        return Inertia::render('desain-data/Show', [
            'breadcrumbs' => $breadcrumbs,
            'data_desain' => $dataDesain,
            'users' => $users,
        ]);
    }

    public function terima($id)
    {
        $dataDesain = DataDesain::findOrFail($id);
        $user = Auth::user();
        $statusId = Kategori::getKategoriId('Status Data Desain', 'Proses');

        $this->authorize('terima', $dataDesain);

        $dataDesain->update([
            'status_id' => $statusId,
            'tgl_terima' => now(),
            'accepted_by_id' => $user->id,
            'accepted_by_name' => $user->nama,
        ]);

        return redirect()->route('desain-data.index')->with('toast', [
            'type' => 'success',
            'message' => 'Order berhasil diterima.',
        ]);
    }

    public function batal($id)
    {
        DB::beginTransaction();

        try {
            $dataDesain = DataDesain::with(['order'])->findOrFail($id);
            $order = $dataDesain->order;
            $user = Auth::user();
            $statusBelumDiterimaId = Kategori::getKategoriId('Status Data Desain', 'Belum Diterima');
            $statusProsesId = Kategori::getKategoriId('Status Data Desain', 'Proses');
            $statusSelesaiId = Kategori::getKategoriId('Status Data Desain', 'Selesai');

            $this->authorize('batal', $dataDesain);

            if ($dataDesain->status_id == $statusProsesId) {
                $dataDesain->update([
                    'status_id' => $statusBelumDiterimaId,
                    'tgl_batal' => now(),
                    'accepted_by_id' => null,
                    'accepted_by_name' => null,
                    'updated_by_id' => $user->id,
                    'updated_by_name' => $user->nama,
                ]);
            } elseif ($dataDesain->status_id == $statusSelesaiId) {
                $dataDesain->update([
                    'status_id' => $statusProsesId,
                    'tgl_batal' => now(),
                    'accepted_by_id' => null,
                    'accepted_by_name' => null,
                    'updated_by_id' => $user->id,
                    'updated_by_name' => $user->nama,
                ]);
            }

            $statusOrderId = $order->statusOrderTerakhir();
            if($statusOrderId){
                $order->update(['status_id' => $statusOrderId]);
            }

            DB::commit();

            return redirect()->route('desain-data.index')->with('toast', [
                'type' => 'success',
                'message' => 'Order berhasil dibatalkan.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal membatalkan data desain: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'updated_by_id' => 'required|exists:users,id',
            'keterangan' => 'nullable|string',
            'feedback' => 'nullable|string',
            'file_riwayat_data_desain' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        DB::beginTransaction();

        try {
            $dataDesain = DataDesain::findOrFail($id);
            $user = User::findOrFail($request->updated_by_id);

            $this->authorize('update', $dataDesain);

            if ($request->filled('riwayat_data_desain_id')) {
                $riwayat = RiwayatDataDesain::findOrFail($request->riwayat_data_desain_id);

                $riwayat->update([
                    'updated_by_id' => $request->updated_by_id,
                    'updated_by_name' => $user->nama,
                    'tgl_feedback' => $request->filled('feedback') ? now() : null,
                    'keterangan' => $request->keterangan,
                    'feedback' => $request->feedback,
                ]);

                // Hapus file lama jika ada file baru
                if ($request->hasFile('file_riwayat_data_desain')) {
                    $riwayat->clearMediaCollection('file_riwayat_data_desain');

                    $riwayat
                        ->addMediaFromRequest('file_riwayat_data_desain')
                        ->usingName('file_riwayat_data_desain')
                        ->toMediaCollection('file_riwayat_data_desain');
                }

            } else {
                // ✅ Buat riwayat baru
                $riwayat = RiwayatDataDesain::create([
                    'data_desain_id' => $dataDesain->id,
                    'updated_by_id' => $request->updated_by_id,
                    'updated_by_nama' => $user->nama,
                    'tgl_feedback' => $request->filled('feedback') ? now() : null,
                    'keterangan' => $request->keterangan,
                    'feedback' => $request->feedback,
                ]);

                // ✅ Upload file jika ada
                if ($request->hasFile('file_riwayat_data_desain')) {
                    $riwayat
                        ->addMediaFromRequest('file_riwayat_data_desain')
                        ->usingName('file_riwayat_data_desain')
                        ->toMediaCollection('file_riwayat_data_desain');
                }
            }

            DB::commit();
            return redirect()->route('desain-data.show', $dataDesain->id)->with('toast', [
                'type' => 'success',
                'message' => 'Data berhasil diperbarui.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal membuat data desain: ' . $e->getMessage()]);
        }
    }

    public function selesai($id)
    {
        DB::beginTransaction();

        try {
            $dataDesain = DataDesain::with([
                'order',
            ])->findOrFail($id);
            $order = $dataDesain->order;
            $user = Auth::user();
            $statusId = Kategori::getKategoriId('Status Data Desain', 'Selesai');
            $statusPembayaranOrder = Kategori::where('id', $order->statusPembayaranOrderTerakhir())
            ->first();

            $this->authorize('selesai', $dataDesain);

            $dataDesain->update([
                'status_id' => $statusId,
                'tgl_selesai' => now(),
                'updated_by_id' => $user->id,
                'updated_by_name' => $user->nama,
            ]);

            if($statusPembayaranOrder && in_array($statusPembayaranOrder->nama, ['DP Produksi', 'Lunas'])) {
                $cetakPrintExists = CetakPrint::where('order_id', $order->id_order)->exists();
                if (!$cetakPrintExists) {
                    $statusCetakId = Kategori::getKategoriId('Status Cetak Print', 'Belum Diterima');
                    CetakPrint::create([
                        'order_id' => $order->id_order,
                        'status_id' => $statusCetakId,
                    ]);
                }
            }

            $statusOrderId = $order->statusOrderTerakhir();
            if($statusOrderId){
                $order->update(['status_id' => $statusOrderId]);
            }

            DB::commit();

            return redirect()->route('desain-data.index')->with('toast', [
                'type' => 'success',
                'message' => 'Data desain berhasil ditandai selesai.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors(['error' => 'Gagal menyelesaikan data desain: ' . $e->getMessage()]);
        }
    }
}
