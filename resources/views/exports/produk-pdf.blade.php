<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Produk</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .timestamp {
            text-align: center;
            font-size: 11px;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #444;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background-color: #eee;
        }
        tfoot td {
            font-weight: bold;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>

    <h2>Laporan Data Produk {{ $month }}/{{ $year }}</h2>
    <div class="timestamp">
        Dicetak pada: {{ \Carbon\Carbon::now()->format('d-m-Y H:i:s') }}
    </div>

    @php
        $totalTerjual = 0;
        $grandTotal = 0;
    @endphp

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>ID Produk</th>
                <th>Nama</th>
                <th>Kategori</th>
                <th>Satuan</th>
                {{-- <th>Stok</th> --}}
                <th>Harga</th>
                <th>Total Terjual</th>
                <th>Sub Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($produks as $index => $produk)
                @php
                    $subtotal = ($produk->total_terjual ?? 0) * $produk->harga;
                    $totalTerjual += $produk->total_terjual ?? 0;
                    $grandTotal += $subtotal;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $produk->id_produk }}</td>
                    <td>{{ $produk->nama }}</td>
                    <td>{{ $produk->kategori->nama ?? '-' }}</td>
                    <td>{{ $produk->satuan->nama ?? '-' }}</td>
                    {{-- <td>{{ $produk->stok }}</td> --}}
                    <td>Rp {{ number_format($produk->harga, 0, ',', '.') }}</td>
                    <td>{{ $produk->total_terjual ?? 0 }}</td>
                    <td>Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center;">Tidak ada data produk.</td>
                </tr>
            @endforelse
        </tbody>

        @if(count($produks) > 0)
        <tfoot>
            <tr>
                <td colspan="6" style="text-align: center;">TOTAL</td>
                <td>{{ $totalTerjual }}</td>
                <td>Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

</body>
</html>
