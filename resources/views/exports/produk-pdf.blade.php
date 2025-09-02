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
    </style>
</head>
<body>

    <h2>Laporan Data Produk {{ $month }}/{{ $year }}</h2>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>ID Produk</th>
                <th>Nama</th>
                <th>Harga</th>
                {{-- <th>Stok</th> --}}
                <th>Total Terjual</th>
                <th>Kategori</th>
                <th>Satuan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($produks as $index => $produk)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $produk->id_produk }}</td>
                    <td>{{ $produk->nama }}</td>
                    <td>Rp{{ number_format($produk->harga, 0, ',', '.') }}</td>
                    {{-- <td>{{ $produk->stok }}</td> --}}
                    <td>{{ $produk->total_qty ?? '-' }}</td>
                    <td>{{ $produk->kategori->nama ?? '-' }}</td>
                    <td>{{ $produk->satuan->nama ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">Tidak ada data produk.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
