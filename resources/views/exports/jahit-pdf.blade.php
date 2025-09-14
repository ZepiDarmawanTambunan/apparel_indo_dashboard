<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Jahit</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .timestamp {
            text-align: center;
            font-size: 11px;
            margin-bottom: 15px;
        }
        .no-data { text-align: center; font-style: italic; padding: 20px; }
    </style>
</head>
<body>
    <h2>Laporan Jahit Bulan {{ $month }}/{{ $year }}</h2>
    <div class="timestamp">
        Dicetak pada: {{ \Carbon\Carbon::now()->format('d-m-Y H:i:s') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tgl Input</th>
                <th>ID Order</th>
                <th>Kategori (Status)</th>
                <th>Petugas</th>
                <th>Nama Barang</th>
                <th>Jumlah Dikerjakan</th>
                <th>Upah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($jahits as $i => $jahit)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($jahit->created_at)->format('d-m-Y') }}</td>
                    <td>{{ $jahit->jahit->order_id ?? '-' }}</td>
                    <td>{{ $jahit->jahit->status->nama ?? '-' }}</td>
                    <td>{{ $jahit->user_nama }}</td>
                    <td>{{ $jahit->produk_nama }}</td>
                    <td>{{ $jahit->jumlah_dikerjakan }}</td>
                    <td>Rp {{ number_format($jahit->salary, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="no-data">Tidak ada data jahit untuk bulan ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
