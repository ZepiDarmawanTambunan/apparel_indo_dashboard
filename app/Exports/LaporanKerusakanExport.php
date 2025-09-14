<?php

namespace App\Exports;

use App\Models\LaporanKerusakan;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class LaporanKerusakanExport implements WithEvents, WithTitle
{
    protected $month;
    protected $year;
    protected $statusKerusakan;
    protected $statusChecking;

    public function __construct($month, $year, $statusKerusakan = null, $statusChecking = null)
    {
        $this->month = $month;
        $this->year = $year;
        $this->statusKerusakan = $statusKerusakan;
        $this->statusChecking = $statusChecking;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $laporanKerusakan = LaporanKerusakan::with(['order', 'status', 'statusChecking'])
                    ->whereMonth('created_at', $this->month)
                    ->whereYear('created_at', $this->year)
                    ->when($this->statusKerusakan, fn ($q) => $q->where('status_id', $this->statusKerusakan))
                    ->when($this->statusChecking, fn ($q) => $q->where('status_checking_id', $this->statusChecking))
                    ->latest()
                    ->get();

                // Judul
                $sheet->mergeCells('A1:I1');
                $sheet->setCellValue('A1', 'Laporan Kerusakan');
                $sheet->mergeCells('A2:I2');
                $sheet->setCellValue('A2', 'Bulan ' . str_pad($this->month, 2, '0', STR_PAD_LEFT) . ' Tahun ' . $this->year);

                // Dicetak pada
                $sheet->mergeCells('A3:I3');
                $sheet->setCellValue('A3', 'Dicetak pada: ' . now()->format('d-m-Y H:i:s'));
                $sheet->getStyle('A3')->getAlignment()->setHorizontal('center');

                // Header tabel di baris 5
                $headers = [
                    'No', 'Order ID', 'Nama Pelanggan', 'Tanggal Laporan',
                    'Divisi Pelapor', 'Status', 'Status Checking', 'Keterangan', 'Jumlah Rusak'
                ];
                $col = 'A';
                foreach ($headers as $header) {
                    $sheet->setCellValue("{$col}5", $header);
                    $col++;
                }

                // Jika data kosong
                if ($laporanKerusakan->isEmpty()) {
                    $sheet->mergeCells('A6:I6');
                    $sheet->setCellValue('A6', 'Tidak ada data untuk ditampilkan');
                    $sheet->getStyle('A6')->applyFromArray([
                        'alignment' => ['horizontal' => 'center'],
                        'font' => ['italic' => true, 'color' => ['rgb' => '888888']]
                    ]);
                    return;
                }

                // Isi data dimulai dari baris 6
                $row = 6;
                foreach ($laporanKerusakan as $index => $item) {
                    $sheet->setCellValue("A{$row}", $index + 1);
                    $sheet->setCellValue("B{$row}", $item->order->id_order ?? '-');
                    $sheet->setCellValue("C{$row}", $item->order->nama_pelanggan ?? '-');
                    $sheet->setCellValue("D{$row}", $item->created_at);
                    $sheet->setCellValue("E{$row}", $item->divisi_pelapor ?? '-');
                    $sheet->setCellValue("F{$row}", $item->status->nama ?? '-');
                    $sheet->setCellValue("G{$row}", $item->statusChecking->nama ?? '-');
                    $sheet->setCellValue("H{$row}", $item->keterangan ?? '-');
                    $sheet->setCellValue("I{$row}", $item->jumlah_rusak ?? 0);
                    $row++;
                }

                // Style judul
                $sheet->getStyle('A1:I3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                ]);

                // Style header
                $sheet->getStyle('A5:I5')->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'EEEEEE'],
                    ],
                    'alignment' => ['horizontal' => 'center'],
                ]);

                // Border
                $sheet->getStyle("A5:I" . ($row - 1))->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                    ],
                ]);

                // Auto size
                foreach (range('A', 'I') as $col) {
                    $sheet->getDelegate()->getColumnDimension($col)->setAutoSize(true);
                }
            }
        ];
    }

    public function title(): string
    {
        return 'Laporan Kerusakan';
    }
}
