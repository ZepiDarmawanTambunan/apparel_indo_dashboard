<?php

namespace App\Exports;

use App\Models\RiwayatCuttingKain;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class CuttingKainExport implements WithEvents, WithTitle
{
    protected $month;
    protected $year;
    protected $kategori;

    public function __construct($month, $year, $kategori = null)
    {
        $this->month = $month;
        $this->year = $year;
        $this->kategori = $kategori;
    }

    public function registerEvents(): array
    {
        $cuttings = RiwayatCuttingKain::query()
            ->select('riwayat_cutting_kain.*')
            ->join('cutting_kain', 'riwayat_cutting_kain.cutting_kain_id', '=', 'cutting_kain.id')
            ->with([
                'cuttingKain.order',
                'cuttingKain.status'
            ])
            ->whereMonth('riwayat_cutting_kain.created_at', $this->month)
            ->whereYear('riwayat_cutting_kain.created_at', $this->year)
            ->when($this->kategori, function ($query) {
                $query->where('cutting_kain.status_id', $this->kategori);
            })
            ->orderBy('cutting_kain.order_id')
            ->get();

        return [
            AfterSheet::class => function (AfterSheet $event) use ($cuttings) {
                $sheet = $event->sheet;

                // Judul
                $sheet->mergeCells('A1:F1');
                $sheet->setCellValue('A1', 'Laporan Data Cutting Kain Bulan ' . $this->month . ' Tahun ' . $this->year);

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => 'center'],
                ]);

                // Header
                $headers = ['No', 'ID Order', 'Status', 'Nama User', 'Jumlah Dikerjakan', 'Tanggal Input'];
                $col = 'A';
                foreach ($headers as $header) {
                    $sheet->setCellValue("{$col}3", $header);
                    $col++;
                }

                // Data
                $row = 4;
                if ($cuttings->isEmpty()) {
                    $sheet->mergeCells("A{$row}:F{$row}");
                    $sheet->setCellValue("A{$row}", 'Tidak ada data cutting kain.');
                    $sheet->getStyle("A{$row}")->getFont()->setItalic(true);
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal('center');
                } else {
                    foreach ($cuttings as $index => $cutting) {
                        $sheet->setCellValue("A{$row}", $index + 1);
                        $sheet->setCellValue("B{$row}", $cutting->cuttingKain->order_id ?? '-');
                        $sheet->setCellValue("C{$row}", $cutting->cuttingKain->status->nama ?? '-');
                        $sheet->setCellValue("D{$row}", $cutting->user_nama ?? '-');
                        $sheet->setCellValue("E{$row}", $cutting->jumlah_dikerjakan);
                        $sheet->setCellValue("F{$row}", \Carbon\Carbon::parse($cutting->created_at)->format('d-m-Y'));
                        $row++;
                    }
                }

                // Header Style
                $sheet->getStyle('A3:F3')->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'EEEEEE'],
                    ],
                    'alignment' => ['horizontal' => 'center'],
                ]);

                // Border
                $lastRow = max($row - 1, 3);
                $sheet->getStyle("A3:F{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                    ],
                ]);

                // Autosize
                foreach (range('A', 'F') as $col) {
                    $sheet->getDelegate()->getColumnDimension($col)->setAutoSize(true);
                }
            }
        ];
    }

    public function title(): string
    {
        return 'Laporan Cutting Kain';
    }
}
