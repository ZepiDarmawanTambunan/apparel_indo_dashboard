<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Order</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        h2 { text-align: center; margin-bottom: 5px; }
        .timestamp { text-align: center; font-size: 11px; margin-bottom: 15px; }
        .no-data { text-align: center; font-style: italic; padding: 20px; }
        .total-row { font-weight: bold; text-align: center }
    </style>
</head>
<body>
    <h2>Laporan Order Bulan {{ $month }}/{{ $year }}</h2>
    <div class="timestamp">
        Dicetak pada: {{ \Carbon\Carbon::now()->format('d-m-Y H:i:s') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>ID Order</th>
                <th>Tanggal</th>
                <th>Pelanggan</th>
                <th>Status Order</th>
                <th>Status Pembayaran</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $i => $order)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $order->id_order }}</td>
                    <td>{{ \Carbon\Carbon::parse($order->created_at)->format('d-m-Y') }}</td>
                    <td>{{ $order->nama_pelanggan }}</td>
                    <td>{{ $order->status->nama ?? '-' }}</td>
                    <td>{{ $order->statusPembayaran->nama ?? '-' }}</td>
                    <td>Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="no-data">Tidak ada data order untuk bulan ini.</td>
                </tr>
            @endforelse
        </tbody>

        @if(!$orders->isEmpty())
            <tfoot>
                <tr class="total-row">
                    <td colspan="6" style="text-align: center;">TOTAL</td>
                    <td>
                        Rp {{ number_format($orders->sum('total'), 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
