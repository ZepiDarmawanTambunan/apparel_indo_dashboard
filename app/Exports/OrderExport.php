<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class OrderExport implements WithEvents, WithTitle
{
    protected $month;
    protected $year;
    protected $statusOrder;
    protected $statusPembayaran;

    public function __construct($month, $year, $statusOrder = null, $statusPembayaran = null)
    {
        $this->month = $month;
        $this->year = $year;
        $this->statusOrder = $statusOrder;
        $this->statusPembayaran = $statusPembayaran;
    }

    public function registerEvents(): array
    {
        $orders = Order::with(['status', 'pembayaran'])
            ->whereMonth('created_at', $this->month)
            ->whereYear('created_at', $this->year)
            ->when($this->statusOrder, function ($query) {
                $query->where('status_id', $this->statusOrder);
            })
            ->when($this->statusPembayaran, function ($query) {
                $query->whereHas('pembayaran', function ($q) {
                    $q->where('status_id', $this->statusPembayaran);
                });
            })
            ->latest()
            ->get();

        return [
            AfterSheet::class => function (AfterSheet $event) use ($orders) {
                $sheet = $event->sheet;

                // Judul
                $sheet->mergeCells('A1:G1');
                $sheet->setCellValue('A1', 'Laporan Order');

                $sheet->mergeCells('A2:G2');
                $sheet->setCellValue('A2', 'Bulan ' . str_pad($this->month, 2, '0', STR_PAD_LEFT) . ' Tahun ' . $this->year);

                $sheet->getStyle('A1:A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                ]);

                // Header kolom
                $headers = ['No', 'ID Order', 'Tanggal', 'Pelanggan', 'Total', 'Status Order', 'Status Pembayaran'];
                $col = 'A';
                foreach ($headers as $header) {
                    $sheet->setCellValue("{$col}4", $header);
                    $col++;
                }

                // Data
                $row = 5;
                if ($orders->isEmpty()) {
                    $sheet->mergeCells("A{$row}:F{$row}");
                    $sheet->setCellValue("A{$row}", 'Tidak ada data order untuk bulan ini.');
                    $sheet->getStyle("A{$row}")->getFont()->setItalic(true);
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal('center');
                } else {
                    foreach ($orders as $index => $order) {
                        $sheet->setCellValue("A{$row}", $index + 1);
                        $sheet->setCellValue("B{$row}", $order->id_order);

                        $tanggalOrder = $order->created_at ? \Carbon\Carbon::parse($order->created_at)->format('d/m/Y') : '-';
                        $sheet->setCellValue("C{$row}", $tanggalOrder);

                        $sheet->setCellValue("D{$row}", $order->nama_pelanggan);
                        $sheet->setCellValue("E{$row}", $order->total);
                        $sheet->getStyle("E{$row}")
                            ->getNumberFormat()
                            ->setFormatCode('"Rp"#,##0');

                        $sheet->setCellValue("F{$row}", $order->status->nama ?? '-');
                        $sheet->setCellValue("G{$row}", $order->statusPembayaran->nama ?? '-');

                        $row++;
                    }
                }

                // Style header
                $sheet->getStyle('A4:G4')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => 'center'],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'EEEEEE'],
                    ],
                ]);

                // Border
                $lastRow = max($row - 1, 4);
                $sheet->getStyle("A4:G{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                    ],
                ]);

                // Autosize
                foreach (range('A', 'G') as $col) {
                    $sheet->getDelegate()->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }

    public function title(): string
    {
        return "Laporan Order {$this->month}-{$this->year}";
    }
}
