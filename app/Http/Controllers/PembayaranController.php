<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Kategori;
use App\Models\Order;
use App\Models\Pembayaran;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PembayaranController extends Controller
{
    public function index()
    {
        $breadcrumbs = [
            ['title' => 'Daftar Pembayaran', 'href' => route('pembayaran.index')],
        ];
        $pembayarans = Pembayaran::with(['kategori', 'status', 'order'])
        ->whereNull('deleted_at')
        ->orderBy('updated_at', 'DESC')
        ->get();
        return Inertia::render('pembayaran/Index', [
            'breadcrumbs' => $breadcrumbs,
            'pembayarans' => $pembayarans
        ]);
    }

    public function show($id_pembayaran)
    {
        $pembayaran = Pembayaran::with(['kategori', 'status', 'order', 'invoice.status'])
            ->where('id_pembayaran', $id_pembayaran)
            ->firstOrFail();

        return Inertia::render('pembayaran/Show', [
            'pembayaran' => $pembayaran,
        ]);
    }

    public function create()
    {
        $kategoriPembayaranParent = Kategori::where('nama', 'Kategori Pembayaran')->first();

        // Status Order
        $statusSelesaiId = Kategori::getKategoriId('Status Order', 'Selesai');
        $statusBatalId = Kategori::getKategoriId('Status Order', 'Batal');
        $statusPembayaranId = Kategori::getKategoriId('Status Pembayaran Order', 'Lunas');

        return Inertia::render('pembayaran/Create', [
            'orders' => Order::with(['statusPembayaran', 'status'])
                            ->whereNotIn('status_id', [$statusBatalId, $statusSelesaiId])
                            ->whereNotIn('status_pembayaran_id', [$statusPembayaranId])
                            ->get(),
            'kategori' => Kategori::select('id', 'nama')
                            ->where('parent_id', $kategoriPembayaranParent->id)
                            ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:order,id_order',
            'kategori_id' => 'required|exists:kategori,id',
            'bayar' => 'required|numeric|min:1',
            'kembalian' => 'required|numeric|min:0',
            'bukti_pembayaran' => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();

        try {
            $order = Order::lockForUpdate()->findOrFail($validated['order_id']);
            $user = Auth::user();

            // Hitung => order.total_pembayaran + (bayar - kembaliian)
            $totalPembayaranBaru = min($order->total_pembayaran + ($validated['bayar'] - $validated['kembalian']), $order->total);

            // Jika order.total = 0 alias langsung dp awal
            if($order->total == 0){
                $totalPembayaranBaru = $validated['bayar'] - $validated['kembalian'];
            }

            // Mendefinisikan pembayaran.kategori
            $kategori = Kategori::findOrFail($validated['kategori_id']);
            $kategoriLunasId = Kategori::getKategoriId('Kategori Pembayaran', 'Lunas');

            // Jika lunas maka totalPembayaranBaru >= order.total
            if (strtolower($kategori->nama) === 'lunas' && $totalPembayaranBaru < $order->total) {
                throw new \Exception('Jumlah pembayaran tidak mencukupi untuk melunasi order.');
            }

            // Jika order.total <= 0 && lunas maka tidak bisa
            if($order->total <= 0 && strtolower($kategori->nama) === 'lunas'){
                throw new \Exception('Silahkan DP terlebih dahulu.');
            }

            // Tentukan pembayaran.kategori && pembayaran.status
            $kategoriId = $order->total > 0 && $totalPembayaranBaru === $order->total
                ? $kategoriLunasId
                : $validated['kategori_id'];
            $kategori = Kategori::findOrFail($kategoriId);
            $statusId = Kategori::getKategoriId('Status Pembayaran', 'Menunggu ACC');

            // Tentukan invoice.status & invoice.kategori
            $statusInvoiceId = Kategori::getKategoriId('Status Invoice', 'Proses');
            $kategoriInvoiceId = Kategori::getKategoriId('Kategori Invoice', $kategori->nama);

            // Create pembayaran
            $pembayaran = Pembayaran::create([
                'id_pembayaran' => $this->getNewIdPembayaran(),
                'order_id' => $order->id_order,
                'bayar' => $validated['bayar'],
                'kembalian' => $validated['kembalian'],
                'kategori_id' => $kategoriId,
                'status_id' => $statusId,
                'user_id' => $user->id,
                'user_nama' => $user->nama,
            ]);

            // Upload bukti pembayaran (Spatie MediaLibrary)
            if ($request->hasFile('bukti_pembayaran')) {
                $pembayaran->addMediaFromRequest('bukti_pembayaran')
                    ->toMediaCollection('bukti_pembayaran');
            }

            // Create invoice
            $invoice = Invoice::create([
                'id_invoice' => $this->getNewIdInvoice(),
                'order_id' => $order->id_order,
                'pembayaran_id' => $pembayaran->id_pembayaran,
                'kategori_id' => $kategoriInvoiceId,
                'status_id' => $statusInvoiceId,
            ]);

            // Update order
            $statusOrderId = $order->statusOrderTerakhir();
            $order->update([
                'total_pembayaran' => $totalPembayaranBaru,
                'status_id' => $statusOrderId,
            ]);

            DB::commit();

            return redirect()->route('pembayaran.show', $pembayaran->id_pembayaran)->with('toast', [
                'type' => 'success',
                'message' => 'Pembayaran berhasil disimpan!',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors([
                'error' => 'Gagal menyimpan pembayaran: ' . $e->getMessage()
            ]);
        }
    }

    public function edit($id_pembayaran)
    {
        $kategoriPembayaranParent = Kategori::where('nama', 'Kategori Pembayaran')->first();
        $pembayaran = Pembayaran::with(['order', 'kategori'])->findOrFail($id_pembayaran);

        $kategoriQuery = Kategori::select('id', 'nama')
            ->where('parent_id', $kategoriPembayaranParent->id);

        if ($pembayaran->order->total <= 0) {
            $kategoriQuery->where('nama', '!=', 'Lunas');
        }

        return Inertia::render('pembayaran/Edit', [
            'pembayaran' => [
                'id_pembayaran' => $pembayaran->id_pembayaran,
                'order_id' => $pembayaran->order_id,
                'kategori_id' => $pembayaran->kategori_id,
                'bayar' => $pembayaran->bayar,
                'kembalian' => $pembayaran->kembalian,
                'order' => $pembayaran->order,
            ],
            'kategori' => $kategoriQuery->get(),
        ]);
    }

    public function update(Request $request, $id_pembayaran)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:order,id_order',
            'kategori_id' => 'required|exists:kategori,id',
            'bayar' => 'required|numeric|min:1',
            'kembalian' => 'required|numeric|min:0',
            'bukti_pembayaran' => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();
            $pembayaran = Pembayaran::with(['order', 'invoice'])->lockForUpdate()->findOrFail($id_pembayaran);
            $order = $pembayaran->order;
            $invoice = $pembayaran->invoice;

            // Hitung total pembayaran baru
            $totalPembayaranBaru = $order->total_pembayaran
                - ($pembayaran->bayar - $pembayaran->kembalian)
                + ($validated['bayar'] - $validated['kembalian']);
            $totalPembayaranBaru = min($totalPembayaranBaru, $order->total);

            // Tentukan pembayaran.kategori
            $kategori = Kategori::findOrFail($validated['kategori_id']);
            $kategoriLunasId = Kategori::getKategoriId('Kategori Pembayaran', 'Lunas');

            if (strtolower($kategori->nama) === 'lunas' && $totalPembayaranBaru < $order->total) {
                throw new \Exception('Jumlah pembayaran tidak mencukupi untuk melunasi order.');
            }

            if($order->total <= 0 && strtolower($kategori->nama) === 'lunas'){
                throw new \Exception('Silahkan DP terlebih dahulu.');
            }

            // Tentukan pembayaran.kategori && pembayaran.status
            $kategoriId = $order->total > 0 && $totalPembayaranBaru === $order->total
                ? $kategoriLunasId
                : $validated['kategori_id'];
            $kategori = Kategori::findOrFail($kategoriId);
            $statusId = Kategori::getKategoriId('Status Pembayaran', 'Menunggu ACC');

            // Tentukan invoice.status & invoice.kategori
            $statusInvoiceLamaId = Kategori::getKategoriId('Status Invoice', 'Batal');
            $kategoriInvoiceBaruId = Kategori::getKategoriId('Kategori Invoice', $kategori->nama);

            // Update pembayaran
            $pembayaran->update([
                'bayar' => $validated['bayar'],
                'kembalian' => $validated['kembalian'],
                'kategori_id' => $kategoriId,
                'status_id' => $statusId,
                'user_id' => $user->id,
                'user_nama' => $user->nama,
            ]);

            // Upload ulang bukti pembayaran (replace media lama)
            if ($request->hasFile('bukti_pembayaran')) {
                $pembayaran->clearMediaCollection('bukti_pembayaran');
                $pembayaran->addMediaFromRequest('bukti_pembayaran')
                    ->toMediaCollection('bukti_pembayaran');
            }


            // Update invoice lama
            $invoice->update([
                'status_id' => $statusInvoiceLamaId,
            ]);

            // Update invoice baru
            $invoice = Invoice::create([
                'pembayaran_id' => $pembayaran->id,
                'order_id' => $order->id,
                'kategori_id' => $kategoriInvoiceBaruId,
                'status_id' => $statusInvoiceLamaId,
            ]);

            // Update order
            $statusOrderId = $order->statusOrderTerakhir();
            $statusPembayaranOrderId = $order->statusPembayaranOrderTerakhir();
            $order->update([
                'total_pembayaran' => $totalPembayaranBaru,
                'status_id' => $statusOrderId,
                'status_pembayaran_id' => $statusPembayaranOrderId,
            ]);

            DB::commit();

            return redirect()->route('pembayaran.index')->with('toast', [
                'type' => 'success',
                'message' => 'Pembayaran berhasil diperbarui!',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors([
                'error' => 'Gagal mengubah pembayaran: ' . $e->getMessage()
            ]);
        }
    }

    public function batal($id_pembayaran)
    {
        DB::beginTransaction();

        try {
            $pembayaran = Pembayaran::with(['order', 'invoice', 'kategori'])->findOrFail($id_pembayaran);
            $order = $pembayaran->order;
            $invoice = $pembayaran->invoice;

            // Validasi 1: Jika pembayaran sudah batal
            if (strtolower($pembayaran->status->nama) === 'batal') {
                throw new \Exception('Pembayaran ini sudah dibatalkan.');
            }

            // Validasi 2: cek apakah pembayaran ini adalah pembayaran terakhir yang aktif
            $pembayaranTerakhir = $order->pembayaran()
                ->where('status_id', '!=', Kategori::getKategoriId('Status Pembayaran', 'Batal'))
                ->latest()
                ->first();
            if ($pembayaranTerakhir->id_pembayaran !== $pembayaran->id_pembayaran) {
                throw new \Exception('Harus membatalkan pembayaran terakhir terlebih dahulu.');
            }

            // Hitung ulang total pembayaran real setelah batal
            $recalculateTotalPembayaran = max(0, $order->total_pembayaran - ($pembayaran->bayar - $pembayaran->kembalian));

            // Update status pembayaran → Batal
            $pembayaran->update([
                'status_id' => Kategori::getKategoriId('Status Pembayaran', 'Batal'),
            ]);

            // Update invoice → batal
            $invoice->update([
                'status_id' => Kategori::getKategoriId('Status Invoice', 'Batal'),
            ]);

            // Update order
            $statusOrderId = $order->statusOrderTerakhir();
            $statusPembayaranOrderId = $order->statusPembayaranOrderTerakhir();
            $order->update([
                'total_pembayaran' => $recalculateTotalPembayaran,
                'status_pembayaran_id' => $statusPembayaranOrderId,
                'status_id' => $statusOrderId,
            ]);

            DB::commit();

            return redirect()->route('pembayaran.index')->with('toast', [
                'type' => 'success',
                'message' => 'Pembayaran berhasil dibatalkan!',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors([
                'error' => 'Gagal membatalkan pembayaran: ' . $e->getMessage()
            ]);
        }
    }

    private function getNewIdPembayaran()
    {
        $existingIdPembayaran = Pembayaran::withTrashed()
            ->pluck('id_pembayaran')->map(function ($item) {
                return (int) substr($item, 3);
            })->toArray();

        $newIdPembayaran = 1;
        while (in_array($newIdPembayaran, $existingIdPembayaran)) {
            $newIdPembayaran++;
        }

        return 'PMB' . str_pad($newIdPembayaran, 5, '0', STR_PAD_LEFT);
    }

    private function getNewIdInvoice()
    {
        $existingIdInvoice = Invoice::withTrashed()
            ->pluck('id_invoice')->map(function ($item) {
                return (int) substr($item, 3);
            })->toArray();

        $newIdInvoice = 1;
        while (in_array($newIdInvoice, $existingIdInvoice)) {
            $newIdInvoice++;
        }

        return 'INV' . str_pad($newIdInvoice, 5, '0', STR_PAD_LEFT);
    }

    protected function mapStatusOrder($pembayaranTerakhirKategori, $orderStatusSekarang)
    {
        return match ($pembayaranTerakhirKategori) {
            'DP Awal' => match ($orderStatusSekarang) {
                'Cetak & Print',
                'Press Kain',
                'Cutting Kain',
                'Jahit',
                'Sablon & Press Kecil',
                'QC',
                'Packaging',
                'Menunggu Tagihan Lunas' => Kategori::getKategoriId('Status Order', $orderStatusSekarang),
                default => Kategori::getKategoriId('Status Order', 'Data Desain'),
            },
            'DP Produksi' => match ($orderStatusSekarang) {
                'Cetak & Print',
                'Press Kain',
                'Cutting Kain',
                'Jahit',
                'Sablon & Press Kecil',
                'QC',
                'Packaging',
                'Menunggu Tagihan Lunas' => Kategori::getKategoriId('Status Order', $orderStatusSekarang),
                default => Kategori::getKategoriId('Status Order', 'Menunggu Tagihan Lunas'),
            },
            default => Kategori::getKategoriId('Status Order', 'Menunggu DP Awal'),
        };
    }
}
