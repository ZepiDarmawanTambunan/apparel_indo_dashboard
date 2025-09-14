<?php

namespace App\Exports;

use App\Models\RiwayatJahit;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class JahitExport implements WithEvents, WithTitle
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
        $jahits = RiwayatJahit::query()
            ->select('riwayat_jahit.*')
            ->join('jahit', 'riwayat_jahit.jahit_id', '=', 'jahit.id')
            ->with([
                'jahit.order',
                'jahit.status'
            ])
            ->whereMonth('riwayat_jahit.created_at', $this->month)
            ->whereYear('riwayat_jahit.created_at', $this->year)
            ->when($this->kategori, fn ($q) => $q->where('jahit.status_id', $this->kategori))
            ->orderBy('jahit.order_id')
            ->get();

        return [
            AfterSheet::class => function (AfterSheet $event) use ($jahits) {
                $sheet = $event->sheet;

                // Judul
                $sheet->mergeCells('A1:G1');
                $sheet->setCellValue('A1', 'Laporan Data Jahit Bulan ' . $this->month . ' Tahun ' . $this->year);
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => 'center'],
                ]);

                // Dicetak pada
                $sheet->mergeCells('A2:G2');
                $sheet->setCellValue('A2', 'Dicetak pada: ' . Carbon::now()->format('d-m-Y H:i:s'));
                $sheet->getStyle('A2')->applyFromArray([
                    'alignment' => ['horizontal' => 'center'],
                    'font' => ['italic' => true, 'size' => 10],
                ]);

                // Header
                $headers = ['No', 'Tgl Input', 'ID Order', 'Status', 'Petugas', 'Jumlah Dikerjakan', 'Upah'];
                $col = 'A';
                foreach ($headers as $header) {
                    $sheet->setCellValue("{$col}3", $header);
                    $col++;
                }

                // Data
                $row = 4;
                if ($jahits->isEmpty()) {
                    $sheet->mergeCells("A{$row}:G{$row}");
                    $sheet->setCellValue("A{$row}", 'Tidak ada data jahit.');
                    $sheet->getStyle("A{$row}")->getFont()->setItalic(true);
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal('center');
                } else {
                    foreach ($jahits as $index => $jahit) {
                        $sheet->setCellValue("A{$row}", $index + 1);
                        $sheet->setCellValue("B{$row}", Carbon::parse($jahit->created_at)->format('d-m-Y'));
                        $sheet->setCellValue("C{$row}", $jahit->jahit->order_id ?? '-');
                        $sheet->setCellValue("D{$row}", $jahit->jahit->status->nama ?? '-');
                        $sheet->setCellValue("E{$row}", $jahit->user_nama ?? '-');
                        $sheet->setCellValue("F{$row}", $jahit->jumlah_dikerjakan);
                        $sheet->setCellValue("G{$row}", 'Rp ' . number_format($jahit->salary, 0, ',', '.'));
                        $row++;
                    }
                }

                // Header Style
                $sheet->getStyle('A3:G3')->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'EEEEEE'],
                    ],
                    'alignment' => ['horizontal' => 'center'],
                ]);

                // Border
                $lastRow = max($row - 1, 3);
                $sheet->getStyle("A3:G{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                    ],
                ]);

                // Autosize
                foreach (range('A', 'G') as $col) {
                    $sheet->getDelegate()->getColumnDimension($col)->setAutoSize(true);
                }
            }
        ];
    }

    public function title(): string
    {
        return 'Laporan Jahit';
    }
}
