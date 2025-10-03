<?php

namespace App\Http\Controllers;

use App\Models\DataDesain;
use App\Models\Kategori;
use App\Models\LaporanKerusakan;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderTambahan;
use App\Models\Produk;
use App\Models\RiwayatDataDesain;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
   public function index()
    {
        $user = Auth::user();
        $breadcrumbs = [
            ['title' => 'Menu', 'href' => route('dashboard')],
            ['title' => 'Order', 'href' => route('order.index')],
        ];
        $orders = Order::with(['status', 'statusPembayaran'])
            ->whereNull('deleted_at')
            ->orderBy('updated_at', 'DESC')
            ->get();

        return Inertia::render('order/Index', [
            'breadcrumbs' => $breadcrumbs,
            'orders' => $orders,
            'can' => [
                'create' => $user->can('buat-order'),
                'edit' => $user->can('edit-order'),
                'cancel' => $user->can('batal-order'),
            ]
        ]);
    }

    public function create()
    {
        $breadcrumbs = [
            ['title' => 'Menu', 'href' => route('dashboard')],
            ['title' => 'Order', 'href' => route('order.index')],
            ['title' => 'Buat', 'href' => route('order.create')],
        ];
        return Inertia::render('order/Create', [
            'breadcrumbs' => $breadcrumbs,
            'produks' => Produk::with(['kategori', 'satuan'])
                ->where('stok', '>', 0)
                ->get(),
        ]);
    }

    public function show($id_order)
    {
        $order = Order::with([
            'status',
            'statusPembayaran',
            'user',
            'orderDetail.orderTambahan',
            'dataDesain',
        ])
        ->where('id_order', $id_order)
        ->firstOrFail();
        $breadcrumbs = [
            ['title' => 'Menu', 'href' => route('dashboard')],
            ['title' => 'Order', 'href' => route('order.index')],
            ['title' => 'Detil', 'href' => route('order.show', $order->id_order)],
        ];

        $riwayatDataDesainTerakhir = null;
        if ($order->dataDesain) {
            $riwayatDataDesainTerakhir = RiwayatDataDesain::where('data_desain_id', $order->dataDesain->id)
                ->latest('created_at')
                ->first();
        }
        $order->riwayat_data_desain = $riwayatDataDesainTerakhir;

        return Inertia::render('order/Show', [
            'breadcrumbs' => $breadcrumbs,
            'order' => $order,
        ]);
    }

    public function tracking($id_order)
    {
        $order = Order::with('status')->findOrFail($id_order);

        return Inertia::render('order/TrackingOrder', [
            'current_status' => $order->status->nama,
        ]);
    }

    public function pembayaran($id_order)
    {
        $order = Order::with([
            'pembayaran' => function ($query) {
                $query->orderBy('created_at', 'DESC');
            },
            'pembayaran.kategori',
            'pembayaran.status',
        ])->findOrFail($id_order);
        $breadcrumbs = [
            ['title' => 'Menu', 'href' => route('dashboard')],
            ['title' => 'Order', 'href' => route('order.index')],
            ['title' => 'Detil', 'href' => route('order.show', $order->id_order)],
            ['title' => 'Pembayaran', 'href' => route('order.pembayaran', $order->id_order)],
        ];

        return Inertia::render('order/PembayaranOrder', [
            'breadcrumbs' => $breadcrumbs,
            'order_id' => $order->id_order,
            'pembayarans' => $order->pembayaran,
        ]);
    }

    public function kerusakan($id_order)
    {
        $laporan_kerusakans = LaporanKerusakan::with(['order', 'status', 'statusChecking'])
            ->where('order_id', $id_order)
            ->get();
        $breadcrumbs = [
            ['title' => 'Menu', 'href' => route('dashboard')],
            ['title' => 'Order', 'href' => route('order.index')],
            ['title' => 'Detil', 'href' => route('order.show', $id_order)],
            ['title' => 'Kerusakan', 'href' => route('order.kerusakan', $id_order)],
        ];
        return Inertia::render('order/KerusakanOrder', [
            'breadcrumbs' => $breadcrumbs,
            'laporan_kerusakans' => $laporan_kerusakans,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pelanggan' => 'required',
            'nohp_wa' => 'required',
            'tgl_deadline' => 'required',
            'keterangan' => 'required',
            'sub_total' => 'required',
            'lainnya' => 'required',
            'diskon' => 'required',
            'total' => 'required',

            'order_detail' => 'nullable|array',
            'order_detail.*.id' => 'required|string',
            'order_detail.*.order_id' => 'required|string', // temp id, untuk relasi internal
            'order_detail.*.produk_id' => 'required|string',
            'order_detail.*.nama' => 'required|string',
            'order_detail.*.kategori' => 'required|string',
            'order_detail.*.satuan' => 'required|string',
            'order_detail.*.qty' => 'required|numeric',
            'order_detail.*.harga' => 'required|numeric',
            'order_detail.*.total' => 'required|numeric',

            'order_tambahan' => 'nullable|array',
            'order_tambahan.*.order_detail_id' => 'required|string', // temp id, untuk relasi internal saja
            'order_tambahan.*.produk_id' => 'required|string',
            'order_tambahan.*.nama' => 'required|string',
            'order_tambahan.*.kategori' => 'required|string',
            'order_tambahan.*.satuan' => 'required|string',
            'order_tambahan.*.qty' => 'required|numeric',
            'order_tambahan.*.harga' => 'required|numeric',
            'order_tambahan.*.total' => 'required|numeric',
        ]);

        DB::beginTransaction();

        try {
            $produkIds = collect($validated['order_detail'] ?? [])->pluck('produk_id')
            ->merge(collect($validated['order_tambahan'] ?? [])->pluck('produk_id'))
            ->unique();

            $produkList = Produk::whereIn('id_produk', $produkIds)->lockForUpdate()->get()->keyBy('id_produk');

            // VALIDASI STOK ORDER DETAIL
            foreach ($validated['order_detail'] ?? [] as $item) {
                $produk = $produkList->get($item['produk_id']);
                if (!$produk) {
                    throw new \Exception("Produk '{$item['nama']}' tidak ditemukan.");
                }
                // if ($produk->stok < $item['qty']) {
                //     throw new \Exception("Stok produk '{$produk->nama}' hanya {$produk->stok}.");
                // }
            }

            // VALIDASI STOK ORDER TAMBAHAN
            foreach ($validated['order_tambahan'] ?? [] as $item) {
                $produk = $produkList->get($item['produk_id']);
                if (!$produk) {
                    throw new \Exception("Produk tambahan '{$item['nama']}' tidak ditemukan.");
                }
                // if ($produk->stok < $item['qty']) {
                //     throw new \Exception("Stok produk tambahan '{$produk->nama}' hanya {$produk->stok}.");
                // }
            }

            // SIMPAN ORDER
            $statusId = Kategori::getKategoriId('Status Order', 'Menunggu DP Awal');
            $statusPembayaranId = Kategori::getKategoriId('Status Pembayaran Order', 'Belum Bayar');
            $user = Auth::user();
            $order = Order::create([
                'id_order' => $this->getNewIdOrder(),
                'nama_pelanggan' => $validated['nama_pelanggan'],
                'nohp_wa' => $validated['nohp_wa'],
                'tgl_deadline' => $validated['tgl_deadline'] ?? null,
                'keterangan' => $validated['keterangan'] ?? null,
                'sub_total' => $validated['sub_total'],
                'lainnya' => $validated['lainnya'],
                'diskon' => $validated['diskon'],
                'total' => $validated['total'],
                'sisa_bayar' => $validated['total'],
                'user_id' => $user->id,
                'user_nama' => $user->nama,
                'status_id' => $statusId,
                'status_pembayaran_id' => $statusPembayaranId,
            ]);

            // Siapkan order_tambahan grouped by order_detail_id (temp id)
            $orderTambahanGrouped = collect($validated['order_tambahan'] ?? [])->groupBy('order_detail_id');

            foreach ($validated['order_detail'] ?? [] as $item) {
                // Create OrderDetail → dapatkan ID asli dari database
                $orderDetail = OrderDetail::create([
                    'order_id' => $order->id_order,
                    'produk_id' => $item['produk_id'],
                    'nama' => $item['nama'],
                    'kategori' => $item['kategori'],
                    'satuan' => $item['satuan'],
                    'qty' => $item['qty'],
                    'harga' => $item['harga'],
                    'total' => $item['total'],
                    'user_id' => $user->id,
                    'user_nama' => $user->nama,
                ]);

                // Cari tambahanItems yang relasinya ke item ini (pakai temp id)
                foreach ($orderTambahanGrouped->get($item['id']) ?? [] as $tambahan) {
                    OrderTambahan::create([
                        'order_detail_id' => $orderDetail->id, // id asli dari DB
                        'produk_id' => $tambahan['produk_id'],
                        'nama' => $tambahan['nama'],
                        'kategori' => $tambahan['kategori'],
                        'satuan' => $tambahan['satuan'],
                        'qty' => $tambahan['qty'],
                        'harga' => $tambahan['harga'],
                        'total' => $tambahan['total'],
                        'user_id' => $user->id,
                        'user_nama' => $user->nama,
                    ]);
                }
            }

            // UPDATE STOK PRODUK
            // foreach ($validated['order_detail'] ?? [] as $item) {
            //     $produk = $produkList->get($item['produk_id']);
            //     $produk->decrement('stok', $item['qty']);
            // }

            // foreach ($validated['order_tambahan'] ?? [] as $item) {
            //     $produk = $produkList->get($item['produk_id']);
            //     $produk->decrement('stok', $item['qty']);
            // }

            DB::commit();

            return redirect()->route('order.index')->with('toast', [
                'type' => 'success',
                'message' => 'Order berhasil disimpan!',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors([
                'error' => 'Gagal membuat order: ' . $e->getMessage()
            ]);
        }
    }

    public function edit($id_order)
    {
        $order = Order::with([
            'status',
            'statusPembayaran',
            'user',
            'orderDetail.orderTambahan'
        ])
        ->where('id_order', $id_order)
        ->firstOrFail();
        $breadcrumbs = [
            ['title' => 'Menu', 'href' => route('dashboard')],
            ['title' => 'Order', 'href' => route('order.index')],
            ['title' => 'Edit', 'href' => route('order.edit', $id_order)],
        ];
        $produks = Produk::with(['kategori', 'satuan'])
            ->where('stok', '>', 0)
            ->get();

        return Inertia::render('order/Edit', [
            'breadcrumbs' => $breadcrumbs,
            'order' => $order,
            'produks' => $produks
        ]);
    }

    public function update(Request $request, $id_order)
    {
        $validated = $request->validate([
            'nama_pelanggan' => 'required',
            'nohp_wa' => 'required',
            'tgl_deadline' => 'required',
            'keterangan' => 'required',
            'sub_total' => 'required',
            'lainnya' => 'required',
            'diskon' => 'required',
            'total' => 'required',

            // order detail optional
            'order_detail' => 'nullable|array',
            'order_detail.*.id' => 'nullable',                      // id
            'order_detail.*.temp_id' => 'nullable',                 // temp id
            'order_detail.*.order_id' => 'required|string',
            'order_detail.*.produk_id' => 'required|string',
            'order_detail.*.nama' => 'required|string',
            'order_detail.*.kategori' => 'required|string',
            'order_detail.*.satuan' => 'required|string',
            'order_detail.*.qty' => 'required|numeric',
            'order_detail.*.harga' => 'required|numeric',
            'order_detail.*.total' => 'required|numeric',

            // order tambahan optional
            'order_tambahan' => 'nullable|array',
            'order_tambahan.*.id' => 'nullable',                    // id
            'order_tambahan.*.temp_id' => 'nullable',               // temp id
            'order_tambahan.*.order_detail_id' => 'nullable',       // id
            'order_tambahan.*.order_detail_temp_id' => 'nullable',  // temp id
            'order_tambahan.*.produk_id' => 'required|string',
            'order_tambahan.*.nama' => 'required|string',
            'order_tambahan.*.kategori' => 'required|string',
            'order_tambahan.*.satuan' => 'required|string',
            'order_tambahan.*.qty' => 'required|numeric',
            'order_tambahan.*.harga' => 'required|numeric',
            'order_tambahan.*.total' => 'required|numeric',
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();
            $order = Order::lockForUpdate()->findOrFail($id_order);

            // Validasi status order & status pembayaran
            if ($order->status?->nama === 'Selesai' && $order->statusPembayaran?->nama === 'Lunas') {
                throw new \Exception("Order tidak bisa diubah karena sudah selesai atau lunas.");
            }

            $statusPembayaranId = null;
            if($order->pembayaranTerakhir() && $order->pembayaranTerakhir()->kategori->nama === 'Lunas'){
                $statusPembayaranId = Kategori::getKategoriId('Status Pembayaran Order', 'DP Produksi');
            }


            $order->update([
                'nama_pelanggan' => $validated['nama_pelanggan'],
                'nohp_wa' => $validated['nohp_wa'],
                'tgl_deadline' => $validated['tgl_deadline'] ?? null,
                'keterangan' => $validated['keterangan'] ?? null,
                'sub_total' => $validated['sub_total'],
                'lainnya' => $validated['lainnya'],
                'diskon' => $validated['diskon'],
                'total' => $validated['total'],
                'user_id' => $user->id,
                'user_nama' => $user->nama,
                'status_pembayaran_id' => $statusPembayaranId ?? $order->status_pembayaran_id,
            ]);

            // VALIDASI PRODUK UTAMA
            // foreach ($validated['order_detail'] ?? [] as $item) {
            //     $produk = Produk::lockForUpdate()->findOrFail($item['produk_id']);

            //     $orderDetail = $order->orderDetail->where('id', $item['id'])->first();
            //     $qtyLama = $orderDetail?->qty ?? 0;

            //     $sisaStok = $produk->stok + $qtyLama - $item['qty'];

            //     if ($sisaStok < 0) {
            //         throw new \Exception("Stok tidak cukup untuk produk {$produk->nama}");
            //     }

            //     $produk->update(['stok' => $sisaStok]);
            // }

            // VALIDASI PRODUK TAMBAHAN
            // foreach ($validated['order_tambahan'] ?? [] as $item) {
            //     $produk = Produk::lockForUpdate()->findOrFail($item['produk_id']);

            //     $orderTambahan = $order->orderDetail->flatMap->orderTambahan->where('id', $item['id'])->first();
            //     $qtyLama = $orderTambahan?->qty ?? 0;

            //     $sisaStok = $produk->stok + $qtyLama - $item['qty'];

            //     if ($sisaStok < 0) {
            //         throw new \Exception("Stok tidak cukup untuk produk tambahan {$produk->nama}");
            //     }

            //     $produk->update(['stok' => $sisaStok]);
            // }

            // Order Detail Ids [..., ..., ..]
            $orderDetailRequest = collect($validated['order_detail'] ?? []);
            $existingDetailIds = $orderDetailRequest->pluck('id')->filter()->toArray();

            // Hapus order_detail yang tidak ada di request (hanya yang punya id)
            $existingDetailIds = $orderDetailRequest->pluck('id')->filter()->toArray();
            if (count($existingDetailIds) > 0) {
                $order->orderDetail()->whereNotIn('id', $existingDetailIds)->delete();
            } else {
                $order->orderDetail()->delete();
            }

            // Mapping temp_id -> id untuk kebutuhan order_tambahan
            $tempIdMap = [];

            // Update Or Create Order Detail
            foreach ($orderDetailRequest as $item) {
                $detail = $order->orderDetail()->updateOrCreate(
                    ['id' => $item['id']], // kalau null → insert baru
                    [
                        'produk_id' => $item['produk_id'],
                        'nama' => $item['nama'],
                        'kategori' => $item['kategori'],
                        'satuan' => $item['satuan'],
                        'qty' => $item['qty'],
                        'harga' => $item['harga'],
                        'total' => $item['total'],
                        'user_id' => $user->id,
                        'user_nama' => $user->nama,
                    ]
                );

                if (!empty($item['temp_id'])) {
                    $tempIdMap[$item['temp_id']] = $detail->id;
                }
            }

            // Order Tambahan Ids [..., ..., ..]
            $orderTambahanRequest = collect($validated['order_tambahan'] ?? []);
            $existingTambahanIds = $orderTambahanRequest->pluck('id')->filter()->toArray();

            // Hapus order_tambahan yang tidak ada di request
            if (count($existingTambahanIds) > 0) {
                OrderTambahan::whereHas('orderDetail', function ($q) use ($order) {
                    $q->where('order_id', $order->id_order);
                })->whereNotIn('id', $existingTambahanIds)->delete();
            } else {
                OrderTambahan::whereHas('orderDetail', function ($q) use ($order) {
                    $q->where('order_id', $order->id_order);
                })->delete();
            }

            // Update Or Create Order Tambahan
            foreach ($orderTambahanRequest as $tambahan) {
                $orderDetailId = $tambahan['order_detail_id']
                    ?? ($tambahan['order_detail_temp_id'] ? ($tempIdMap[$tambahan['order_detail_temp_id']] ?? null) : null);

                if (!$orderDetailId) continue; // abaikan jika tidak ada order_detail yang valid

                OrderTambahan::updateOrCreate(
                    ['id' => $tambahan['id']],
                    [
                        'order_detail_id' => $orderDetailId,
                        'produk_id' => $tambahan['produk_id'],
                        'nama' => $tambahan['nama'],
                        'kategori' => $tambahan['kategori'],
                        'satuan' => $tambahan['satuan'],
                        'qty' => $tambahan['qty'],
                        'harga' => $tambahan['harga'],
                        'total' => $tambahan['total'],
                        'user_id' => $user->id,
                        'user_nama' => $user->nama,
                    ]
                );
            }
            DB::commit();

            return redirect()->route('order.index')->with('toast', [
                'type' => 'success',
                'message' => 'Order berhasil diperbarui!',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors([
                'error' => 'Gagal mengedit order: ' . $e->getMessage()
            ]);
        }
    }

    public function batal($id_order)
    {
        DB::beginTransaction();

        try {
            $order = Order::with(['pembayaran', 'invoice', 'dataDesain', 'status'])->findOrFail($id_order);

            if ($order->status->nama === 'Selesai') {
                throw new \Exception("Order sudah selesai, tidak bisa dibatalkan.");
            }

            if ($order->status->nama === 'Batal') {
                throw new \Exception("Order sudah dibatalkan, tidak bisa dibatalkan lagi.");
            }

            // Update status Order → Batal
            $order->update([
                'status_id' => Kategori::getKategoriId('Status Order', 'Batal'),
            ]);


            // Update semua pembayaran terkait → Batal
            foreach ($order->pembayaran as $pembayaran) {
                $pembayaran->update([
                    'status_id' => Kategori::getKategoriId('Status Pembayaran', 'Batal'),
                ]);
            }

            // Update semua invoice terkait → Batal
            foreach ($order->invoice as $invoice) {
                $invoice->update([
                    'status_id' => Kategori::getKategoriId('Status Invoice', 'Batal'),
                ]);
            }

            // Update Data Desain → Batal
            if($order->dataDesain) {
                $order->dataDesain->update([
                    'status_id' => Kategori::getKategoriId('Status Data Desain', 'Batal'),
                ]);
            }

            // Update Cetak Print → Batal
            if($order->cetakPrint) {
                $order->cetakPrint->update([
                    'status_id' => Kategori::getKategoriId('Status Cetak Print', 'Batal'),
                ]);
            }

            DB::commit();

            return redirect()->route('order.index')->with('toast', [
                'type' => 'success',
                'message' => 'Order berhasil dibatalkan!',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal membatalkan order: ' . $e->getMessage()]);
        }
    }

    private function getNewIdOrder()
    {
        $existingIdOrder = Order::withTrashed()
        ->pluck('id_order')->map(function ($item) {
            return (int) substr($item, 3); // Ambil angka setelah 'ORD'
        })->toArray();

        $newIdOrder = 1;
        while (in_array($newIdOrder, $existingIdOrder)) {
            $newIdOrder++;
        }

        return 'ORD' . str_pad($newIdOrder, 5, '0', STR_PAD_LEFT);
    }
}
