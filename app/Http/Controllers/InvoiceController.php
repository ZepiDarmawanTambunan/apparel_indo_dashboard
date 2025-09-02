<?php

namespace App\Http\Controllers;

use App\Models\CetakPrint;
use App\Models\DataDesain;
use App\Models\Invoice;
use App\Models\Kategori;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    public function index()
    {
        $breadcrumbs = [
            ['title' => 'Daftar Invoice', 'href' => route('invoice.index')],
        ];
        $invoices = Invoice::with([
            'kategori',
            'status',
            'order'
        ])->whereNull('deleted_at')
        ->orderBy('updated_at', 'DESC')
        ->get();
        return Inertia::render('invoice/Index', [
            'invoices' => $invoices,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    public function show($id_invoice)
    {
        return Inertia::render('invoice/Show', [
            'invoice' => Invoice::with([
                'order.orderDetail.orderTambahan',
                'pembayaran',
                'kategori',
                'status'
            ])->findOrFail($id_invoice),
        ]);
    }

    public function konfirmasi(Request $request, $id_invoice)
    {
        $validated = $request->validate([
            'status' => 'required|in:batal,selesai',
            'keterangan' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();

            // Update Invoice
            $invoice = Invoice::with([
                'order',
                'order.dataDesain.status',
                // 'order.qc.status',
                // 'order.packaging.status',
                'pembayaran'
            ])->findOrFail($id_invoice);
            $statusNama = $validated['status'] === 'batal' ? 'Batal' : 'Selesai';
            $invoice->update([
                'status_id'  => Kategori::getKategoriId('Status Invoice', $statusNama),
                'keterangan' => $statusNama === 'Batal' ? ($validated['keterangan'] ?? null) : null,
                'user_id'    => $user->id,
                'user_nama'  => $user->nama,
            ]);

            $pembayaran = $invoice->pembayaran;
            $order = $invoice->order;

            if ($validated['status'] === 'batal') {
                // ==== Jika Invoice dibatalkan ====

                // Validasi Agar Pembatalan Harus Berurutan (Terakhir dulu)
                $pembayaranTerakhir = $order->pembayaran()
                    ->where('status_id', '!=', Kategori::getKategoriId('Status Pembayaran', 'Batal'))
                    ->latest()
                    ->first();
                if ($pembayaranTerakhir->id_pembayaran !== $pembayaran->id_pembayaran) {
                    throw new \Exception('Harus membatalkan pembayaran terakhir terlebih dahulu.');
                }

                // Hitung ulang total pembayaran order
                $recalculateTotalPembayaran = max(0, $order->total_pembayaran - ($pembayaran->bayar - $pembayaran->kembalian));

                // Update Pembayaran
                $pembayaran->update([
                    'status_id' => Kategori::getKategoriId('Status Pembayaran', 'Batal'),
                ]);

                // Update order
                $statusOrderId = $order->statusOrderTerakhir();
                $statusPembayaranOrderId = $order->statusPembayaranOrderTerakhir();
                $order->update([
                    'total_pembayaran' => $recalculateTotalPembayaran,
                    'status_pembayaran_id' => $statusPembayaranOrderId,
                    'status_id' => $statusOrderId,
                ]);
            } else {
                // ==== Jika Invoice dikonfirmasi ====

                // Update Pembayaran
                $pembayaran->update([
                    'status_id' => Kategori::getKategoriId('Status Pembayaran', 'Selesai'),
                ]);

                // Update Order
                $statusOrderId = $order->statusOrderTerakhir();
                $statusPembayaranOrderId = $order->statusPembayaranOrderTerakhir();
                $order->update([
                    'status_id' => $statusOrderId,
                    'status_pembayaran_id' => $statusPembayaranOrderId
                ]);

                // Create Data Desain
                $statusDataDesainId = Kategori::getKategoriId('Status Data Desain', 'Belum Diterima');
                if (!$order->dataDesain) {
                    DataDesain::create([
                        'order_id' => $order->id_order,
                        'status_id' => $statusDataDesainId,
                    ]);
                }

                // DP Produksi & Lunas
                if($order->dataDesain && in_array($order->statusPembayaran->nama ?? '', ['DP Produksi', 'Lunas']) && !$order->cetakPrint){
                    $cetakPrintExists = CetakPrint::where('order_id', $order->id_order)->exists();
                    if (!$cetakPrintExists) {
                        $statusCetakId = Kategori::getKategoriId('Status Cetak Print', 'Proses');
                        CetakPrint::create([
                            'order_id' => $order->id_order,
                            'status_id' => $statusCetakId,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('invoice.index')->with('toast', [
                'type' => 'success',
                'message' => 'Invoice berhasil diperbarui!',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal memperbarui invoice: ' . $e->getMessage()]);
        }
    }

    public function exportPdf($id)
    {
        $invoice = Invoice::with([
                'order.orderDetail.orderTambahan',
                'pembayaran',
                'kategori',
                'status'
        ])->findOrFail($id);

        $pdf = Pdf::loadView('exports.invoice-pdf', compact('invoice'))
        ->setPaper('a3', 'portrait');;
        return $pdf->stream("invoice-{$invoice->id_invoice}.pdf");
    }

    protected function mapStatusOrder($pembayaranTerakhirKategori, $orderStatusSekarang)
    {
        return match ($pembayaranTerakhirKategori) {
            'DP Awal' => match ($orderStatusSekarang) {
                'Proses Produksi', 'QC', 'Packaging', 'Menunggu Tagihan Lunas' => Kategori::getKategoriId('Status Order', $orderStatusSekarang),
                default => Kategori::getKategoriId('Status Order', 'Menunggu Desain'),
            },
            'DP Produksi' => match ($orderStatusSekarang) {
                'Proses Produksi', 'QC', 'Packaging', 'Menunggu Tagihan Lunas' => Kategori::getKategoriId('Status Order', $orderStatusSekarang),
                default => Kategori::getKategoriId('Status Order', 'Menunggu Tagihan Lunas'),
            },
            default => Kategori::getKategoriId('Status Order', 'Menunggu DP Awal'),
        };
    }
}
