<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Order</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        h2 { text-align: center; }
        .no-data { text-align: center; font-style: italic; padding: 20px; }
    </style>
</head>
<body>
    <h2>Laporan Order Bulan {{ $month }}/{{ $year }}</h2>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>ID Order</th>
                <th>Tanggal</th>
                <th>Pelanggan</th>
                <th>Total</th>
                <th>Status Order</th>
                <th>Status Pembayaran</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $i => $order)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $order->id_order }}</td>
                    <td>{{ \Carbon\Carbon::parse($order->created_at)->format('d-m-Y') }}</td>
                    <td>{{ $order->nama_pelanggan }}</td>
                    <td>Rp{{ number_format($order->total, 0, ',', '.') }}</td>
                    <td>{{ $order->status->nama ?? '-' }}</td>
                    <td>{{ $order->statusPembayaran->nama ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="no-data">Tidak ada data order untuk bulan ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
