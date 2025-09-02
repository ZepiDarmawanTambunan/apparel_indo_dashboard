<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;
use App\Models\LaporanKerusakan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LaporanKerusakanController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:order,id_order',
            'divisi_pelapor' => 'required|string',
            'keterangan' => 'nullable|string',
            'jumlah_rusak' => 'required|integer|min:1',
            'foto_kerusakan' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();
            $statusLaporanKerusakan = Kategori::getKategoriId('Status Laporan Kerusakan', 'Proses');
            $statusChecking = Kategori::getKategoriId('Status Checking', 'Pending');
            $laporan = LaporanKerusakan::create([
                'order_id' => $request->order_id,
                'divisi_pelapor' => $request->divisi_pelapor,
                'jumlah_rusak' => $request->jumlah_rusak,
                'keterangan' => $request->keterangan,
                'pelapor_id' => $user->id,
                'pelapor_nama' => $user->nama,
                'status_id' => $statusLaporanKerusakan,
                'status_checking_id' => $statusChecking,
            ]);

            if ($request->hasFile('foto_kerusakan')) {
                $laporan->addMedia($request->file('foto_kerusakan'))->toMediaCollection('foto_kerusakan');
            }

            DB::commit();

            return redirect()->back()->with('toast', [
                'type' => 'success',
                'message' => 'Laporan kerusakan berhasil dikirim.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('toast', [
                'type' => 'error',
                'message' => 'Gagal mengirim laporan kerusakan: ' . $e->getMessage(),
            ]);
        }
    }

    public function batal($id)
    {
        DB::beginTransaction();

        try {
            $laporan = LaporanKerusakan::with(['status'])->findOrFail($id);

            // Status Laporan Kerusakan
            $statusChecking = Kategori::getKategoriId('Status Checking', 'Selesai');
            $statusBatal = Kategori::getKategoriId('Status Laporan Kerusakan', 'Batal');
            // $statusProses = Kategori::getKategoriId('Status Laporan Kerusakan', 'Proses');
            // if ($laporan->status->nama === 'Proses') {
                // $statusProses = Kategori::getKategoriId('Status Laporan Kerusakan', 'Batal');
            // }

            $laporan->update([
                'status_checking_id' => $statusChecking,
                'status_id' => $statusBatal,
            ]);

            DB::commit();

            return redirect()->back()->with('toast', [
                'type' => 'success',
                'message' => 'Status laporan diubah menjadi Proses (dibatalkan).',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('toast', [
                'type' => 'error',
                'message' => 'Gagal membatalkan laporan: ' . $e->getMessage(),
            ]);
        }
    }

    public function selesai($id)
    {
        DB::beginTransaction();

        try {
            $laporan = LaporanKerusakan::findOrFail($id);

            // Status Laporan Kerusakan
            $statusSelesai = Kategori::getKategoriId('Status Laporan Kerusakan', 'Selesai');

            $laporan->update([
                'status_id' => $statusSelesai,
            ]);

            DB::commit();

            return redirect()->back()->with('toast', [
                'type' => 'success',
                'message' => 'Status laporan berhasil diperbarui menjadi selesai.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('toast', [
                'type' => 'error',
                'message' => 'Gagal memperbarui status: ' . $e->getMessage(),
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $laporan = LaporanKerusakan::findOrFail($id);

            $validated = $request->validate([
                'divisi_bertanggung_jawab' => 'required|string',
                'jumlah_rusak' => 'required|integer|min:1',
                'keterangan' => 'nullable|string',
                'keterangan_checking' => 'nullable|string',
                'is_human_error' => 'required|boolean',
            ]);

            $laporan->update($validated);

            DB::commit();

            return redirect()->back()->with('toast', [
                'type' => 'success',
                'message' => 'Berhasil memperbarui laporan',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('toast', [
                'type' => 'error',
                'message' => 'Gagal memperbarui laporan: ' . $e->getMessage(),
            ]);
        }
    }

    public function batalChecking($id)
    {
        DB::beginTransaction();

        try {
            $laporan = LaporanKerusakan::with(['status'])->findOrFail($id);

            $statusChecking = Kategori::getKategoriId('Status Checking', 'Pending');
            $statusProses = Kategori::getKategoriId('Status Laporan Kerusakan', 'Proses');

            $laporan->update([
                'status_checking_id' => $statusChecking,
                'status_id' => $statusProses,
            ]);

            DB::commit();

            return redirect()->back()->with('toast', [
                'type' => 'success',
                'message' => 'Berhasil batal status checking.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('toast', [
                'type' => 'error',
                'message' => 'Gagal membatalkan status checking: ' . $e->getMessage(),
            ]);
        }
    }

    public function selesaiChecking($id)
    {
        DB::beginTransaction();

        try {
            $laporan = LaporanKerusakan::findOrFail($id);

            $statusChecking = Kategori::getKategoriId('Status Checking', 'Selesai');
            $statusSelesai = Kategori::getKategoriId('Status Laporan Kerusakan', 'Selesai');

            $laporan->update([
                'status_checking_id' => $statusChecking,
                'status_id' => $statusSelesai,
            ]);

            DB::commit();

            return redirect()->back()->with('toast', [
                'type' => 'success',
                'message' => 'Berhasil selesai status checking.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('toast', [
                'type' => 'error',
                'message' => 'Gagal memperbarui status checking: ' . $e->getMessage(),
            ]);
        }
    }
}
