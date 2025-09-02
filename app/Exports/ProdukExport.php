<?php

namespace App\Exports;

use App\Models\Produk;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class ProdukExport implements WithEvents, WithTitle
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
       // Ambil semua produk
        $produks = Produk::with(['kategori', 'satuan'])
            ->when($this->kategori, fn($q) => $q->where('kategori_id', $this->kategori))
            ->get();

        // Ambil jumlah qty dari order_detail
        $orderDetailQty = DB::table('order_detail')
            ->join('order', 'order_detail.order_id', '=', 'order.id_order')
            ->select('order_detail.produk_id', DB::raw('SUM(order_detail.qty) as total_qty'))
            ->whereMonth('order.created_at', $this->month)
            ->whereYear('order.created_at', $this->year)
            ->groupBy('order_detail.produk_id')
            ->pluck('total_qty', 'order_detail.produk_id');

        // Ambil jumlah qty dari order_tambahan
        $orderTambahanQty = DB::table('order_tambahan')
            ->join('order_detail', 'order_tambahan.order_detail_id', '=', 'order_detail.id')
            ->join('order', 'order_detail.order_id', '=', 'order.id_order')
            ->select('order_tambahan.produk_id', DB::raw('SUM(order_tambahan.qty) as total_qty'))
            ->whereMonth('order.created_at', $this->month)
            ->whereYear('order.created_at', $this->year)
            ->groupBy('order_tambahan.produk_id')
            ->pluck('total_qty', 'order_tambahan.produk_id');

        // Gabungkan qty ke produk
        foreach ($produks as $produk) {
            $qty1 = $orderDetailQty[$produk->id_produk] ?? 0;
            $qty2 = $orderTambahanQty[$produk->id_produk] ?? 0;
            $produk->total_qty = $qty1 + $qty2;
        }

        return [
            AfterSheet::class => function (AfterSheet $event) use ($produks) {
                $sheet = $event->sheet;

                // Judul
                $sheet->mergeCells('A1:G1');
                $sheet->setCellValue('A1', 'Laporan Data Produk Bulan '.$this->month.' Tahun '.$this->year);

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => 'center'],
                ]);

                // Header
                $headers = ['No', 'ID Produk', 'Nama', 'Harga', 'Total Terjual', 'Kategori', 'Satuan'];
                $col = 'A';
                foreach ($headers as $header) {
                    $sheet->setCellValue("{$col}3", $header);
                    $col++;
                }

                // Data
                $row = 4;
                if ($produks->isEmpty()) {
                    $sheet->mergeCells("A{$row}:G{$row}");
                    $sheet->setCellValue("A{$row}", 'Tidak ada data produk.');
                    $sheet->getStyle("A{$row}")->getFont()->setItalic(true);
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal('center');
                } else {
                    foreach ($produks as $index => $produk) {
                        $sheet->setCellValue("A{$row}", $index + 1);
                        $sheet->setCellValue("B{$row}", $produk->id_produk);
                        $sheet->setCellValue("C{$row}", $produk->nama);
                        $sheet->setCellValue("D{$row}", $produk->harga);
                        $sheet->setCellValue("E{$row}", $produk->total_qty);
                        $sheet->setCellValue("F{$row}", $produk->kategori->nama ?? '-');
                        $sheet->setCellValue("G{$row}", $produk->satuan->nama ?? '-');
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
        return 'Laporan Produk';
    }
}
