<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Kerusakan - {{ $month }}/{{ $year }}</title>
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

    <h2>Laporan Kerusakan <br>Bulan {{ str_pad($month, 2, '0', STR_PAD_LEFT) }} Tahun {{ $year }}</h2>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Order ID</th>
                <th>Nama Pelanggan</th>
                <th>Tanggal Laporan</th>
                <th>Divisi Pelapor</th>
                <th>Jumlah Rusak</th>
                <th>Status Kerusakan</th>
                <th>Status Checking</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($laporanKerusakan as $index => $laporan)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $laporan->order->id_order ?? '-' }}</td>
                    <td>{{ $laporan->order->nama_pelanggan ?? '-' }}</td>
                    <td>{{ $laporan->created_at }}</td>
                    <td>{{ $laporan->divisi_pelapor ?? '-' }}</td>
                    <td>{{ $laporan->jumlah_rusak }}</td>
                    <td>{{ $laporan->status->nama ?? '-' }}</td>
                    <td>{{ $laporan->statusChecking->nama ?? '-' }}</td>
                    <td>{{ $laporan->keterangan ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center;">Tidak ada data kerusakan selesai pada bulan ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
