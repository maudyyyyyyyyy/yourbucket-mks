<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Carbon\Carbon;

class SalesExportController extends Controller
{
    private const PAID_STATUSES = ['paid', 'settlement', 'capture', 'completed', 'delivered'];

    public function export(Request $request)
    {
        $now = Carbon::now();

        // ── Data ──────────────────────────────────────────────
        $totalSales  = Order::whereIn('status', self::PAID_STATUSES)->sum('total_amount');
        $totalOrders = Order::count();
        $completed   = Order::where('status', 'delivered')->count();
        $processing  = Order::where('status', 'processing')->count();

        $topProducts = Product::withCount([
            'orderItems as total_sold' => fn($q) => $q->whereHas('order', fn($q) => $q->whereIn('status', self::PAID_STATUSES))
        ])->withSum([
            'orderItems as revenue' => fn($q) => $q->whereHas('order', fn($q) => $q->whereIn('status', self::PAID_STATUSES))
        ], 'price')
        ->orderByDesc('revenue')
        ->take(20)
        ->get();

        $recentOrders = Order::with(['user'])
            ->latest()
            ->take(50)
            ->get();

        // ── Spreadsheet ───────────────────────────────────────
        $spreadsheet = new Spreadsheet();

        // Style helpers
        $purple     = '7C3AED';
        $white      = 'FFFFFF';
        $darkText   = '111827';
        $grayText   = '6B7280';
        $lightGray  = 'F9FAFB';
        $borderColor= 'E5E7EB';

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => $white], 'name' => 'Arial', 'size' => 10],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $purple]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $borderColor]]],
        ];

        $cellStyle = [
            'font'      => ['name' => 'Arial', 'size' => 10, 'color' => ['rgb' => $darkText]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $borderColor]]],
        ];

        $altStyle = array_merge($cellStyle, [
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F3F4F6']],
        ]);

        // ────────────────────────────────────────────────────
        // SHEET 1 — Ringkasan
        // ────────────────────────────────────────────────────
        $ws1 = $spreadsheet->getActiveSheet();
        $ws1->setTitle('Ringkasan');

        // Title
        $ws1->mergeCells('A1:D1');
        $ws1->setCellValue('A1', 'LAPORAN HASIL PENJUALAN');
        $ws1->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 16, 'name' => 'Arial', 'color' => ['rgb' => $purple]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $ws1->getRowDimension(1)->setRowHeight(36);

        $ws1->mergeCells('A2:D2');
        $ws1->setCellValue('A2', 'Yourbucket MKS — Management Panel');
        $ws1->getStyle('A2')->applyFromArray([
            'font'      => ['name' => 'Arial', 'size' => 10, 'color' => ['rgb' => $grayText]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $ws1->mergeCells('A3:D3');
        $ws1->setCellValue('A3', 'Digenerate: ' . $now->format('d F Y, H:i'));
        $ws1->getStyle('A3')->applyFromArray([
            'font'      => ['name' => 'Arial', 'size' => 9, 'color' => ['rgb' => $grayText], 'italic' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $ws1->getRowDimension(3)->setRowHeight(20);

        // Stats header
        $ws1->setCellValue('A5', 'RINGKASAN STATISTIK');
        $ws1->getStyle('A5')->applyFromArray([
            'font' => ['bold' => true, 'name' => 'Arial', 'size' => 11, 'color' => ['rgb' => '1E1B4B']],
        ]);
        $ws1->getRowDimension(5)->setRowHeight(22);

        $stats = [
            ['Pendapatan Total',    'Rp ' . number_format($totalSales, 0, ',', '.')],
            ['Total Orders',        number_format($totalOrders)],
            ['Order Selesai',       number_format($completed)],
            ['Sedang Diproses',     number_format($processing)],
            ['Periode Export',      $now->format('d F Y')],
        ];

        foreach ($stats as $i => [$label, $value]) {
            $row = 6 + $i;
            $ws1->setCellValue("A{$row}", $label);
            $ws1->setCellValue("B{$row}", $value);
            $ws1->getStyle("A{$row}")->applyFromArray([
                'font'    => ['bold' => true, 'name' => 'Arial', 'size' => 10, 'color' => ['rgb' => '374151']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $borderColor]]],
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $lightGray]],
            ]);
            $ws1->getStyle("B{$row}")->applyFromArray([
                'font'    => ['bold' => true, 'name' => 'Arial', 'size' => 10, 'color' => ['rgb' => $purple]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $borderColor]]],
            ]);
            $ws1->getRowDimension($row)->setRowHeight(22);
        }

        $ws1->getColumnDimension('A')->setWidth(28);
        $ws1->getColumnDimension('B')->setWidth(28);

        // ────────────────────────────────────────────────────
        // SHEET 2 — Top Produk
        // ────────────────────────────────────────────────────
        $ws2 = $spreadsheet->createSheet();
        $ws2->setTitle('Top Produk');

        $ws2->mergeCells('A1:E1');
        $ws2->setCellValue('A1', 'TOP PRODUK TERLARIS');
        $ws2->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'name' => 'Arial', 'color' => ['rgb' => $purple]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $ws2->getRowDimension(1)->setRowHeight(32);

        $ws2->mergeCells('A2:E2');
        $ws2->setCellValue('A2', 'Berdasarkan total pendapatan dari pesanan berhasil');
        $ws2->getStyle('A2')->applyFromArray([
            'font'      => ['name' => 'Arial', 'size' => 9, 'color' => ['rgb' => $grayText], 'italic' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Header row
        $headers2 = ['No', 'Nama Produk', 'Total Terjual', 'Pendapatan (Rp)', '% dari Total'];
        foreach ($headers2 as $col => $h) {
            $ws2->setCellValueByColumnAndRow($col + 1, 3, $h);
        }
        $ws2->getStyle('A3:E3')->applyFromArray($headerStyle);
        $ws2->getRowDimension(3)->setRowHeight(24);

        $totalRev = $topProducts->sum('revenue') ?: 1;
        foreach ($topProducts as $i => $product) {
            $row   = $i + 4;
            $style = $i % 2 === 0 ? $cellStyle : $altStyle;
            $pct   = $totalRev > 0 ? round(($product->revenue / $totalRev) * 100, 1) : 0;

            $ws2->setCellValueByColumnAndRow(1, $row, $i + 1);
            $ws2->setCellValueByColumnAndRow(2, $row, $product->name);
            $ws2->setCellValueByColumnAndRow(3, $row, $product->total_sold ?? 0);
            $ws2->setCellValueByColumnAndRow(4, $row, $product->revenue ?? 0);
            $ws2->setCellValueByColumnAndRow(5, $row, $pct . '%');
            $ws2->getStyle("A{$row}:E{$row}")->applyFromArray($style);

            // Format currency
            $ws2->getStyle("D{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $ws2->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $ws2->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $ws2->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $ws2->getRowDimension($row)->setRowHeight(20);
        }

        // Total row
        $lastRow2 = count($topProducts) + 4;
        $ws2->setCellValue("B{$lastRow2}", 'TOTAL');
        $ws2->setCellValue("C{$lastRow2}", '=SUM(C4:C' . ($lastRow2 - 1) . ')');
        $ws2->setCellValue("D{$lastRow2}", '=SUM(D4:D' . ($lastRow2 - 1) . ')');
        $ws2->setCellValue("E{$lastRow2}", '100%');
        $ws2->getStyle("A{$lastRow2}:E{$lastRow2}")->applyFromArray([
            'font'    => ['bold' => true, 'name' => 'Arial', 'size' => 10, 'color' => ['rgb' => $white]],
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $purple]],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $borderColor]]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $ws2->getStyle("D{$lastRow2}")->getNumberFormat()->setFormatCode('#,##0');
        $ws2->getRowDimension($lastRow2)->setRowHeight(22);

        $ws2->getColumnDimension('A')->setWidth(6);
        $ws2->getColumnDimension('B')->setWidth(35);
        $ws2->getColumnDimension('C')->setWidth(16);
        $ws2->getColumnDimension('D')->setWidth(22);
        $ws2->getColumnDimension('E')->setWidth(16);

        // ────────────────────────────────────────────────────
        // SHEET 3 — Pesanan
        // ────────────────────────────────────────────────────
        $ws3 = $spreadsheet->createSheet();
        $ws3->setTitle('Pesanan Terbaru');

        $ws3->mergeCells('A1:G1');
        $ws3->setCellValue('A1', 'DAFTAR PESANAN TERBARU');
        $ws3->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'name' => 'Arial', 'color' => ['rgb' => $purple]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $ws3->getRowDimension(1)->setRowHeight(32);

        $headers3 = ['No', 'Kode Order', 'Customer', 'Total (Rp)', 'Status', 'Pengiriman', 'Tanggal'];
        foreach ($headers3 as $col => $h) {
            $ws3->setCellValueByColumnAndRow($col + 1, 3, $h);
        }
        $ws3->getStyle('A3:G3')->applyFromArray($headerStyle);
        $ws3->getRowDimension(3)->setRowHeight(24);

        $statusLabel = [
            'paid'       => 'Sudah Dibayar',
            'processing' => 'Diproses',
            'shipped'    => 'Dikirim',
            'delivered'  => 'Selesai',
            'pending'    => 'Pending',
        ];

        $shippingLabel = [
            'standard' => 'Standard',
            'instant'  => 'Instant',
            'pickup'   => 'Ambil di Tempat',
        ];

        foreach ($recentOrders as $i => $order) {
            $row   = $i + 4;
            $style = $i % 2 === 0 ? $cellStyle : $altStyle;

            $ws3->setCellValueByColumnAndRow(1, $row, $i + 1);
            $ws3->setCellValueByColumnAndRow(2, $row, $order->order_code);
            $ws3->setCellValueByColumnAndRow(3, $row, $order->user?->name ?? '-');
            $ws3->setCellValueByColumnAndRow(4, $row, $order->total_amount);
            $ws3->setCellValueByColumnAndRow(5, $row, $statusLabel[$order->status] ?? ucfirst($order->status));
            $ws3->setCellValueByColumnAndRow(6, $row, $shippingLabel[$order->shipping_type] ?? ucfirst($order->shipping_type ?? '-'));
            $ws3->setCellValueByColumnAndRow(7, $row, $order->created_at->format('d/m/Y'));

            $ws3->getStyle("A{$row}:G{$row}")->applyFromArray($style);
            $ws3->getStyle("D{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $ws3->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $ws3->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $ws3->getRowDimension($row)->setRowHeight(20);
        }

        // Total
        $lastRow3 = count($recentOrders) + 4;
        $ws3->setCellValue("C{$lastRow3}", 'TOTAL');
        $ws3->setCellValue("D{$lastRow3}", '=SUM(D4:D' . ($lastRow3 - 1) . ')');
        $ws3->getStyle("A{$lastRow3}:G{$lastRow3}")->applyFromArray([
            'font'    => ['bold' => true, 'name' => 'Arial', 'size' => 10, 'color' => ['rgb' => $white]],
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $purple]],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $borderColor]]],
        ]);
        $ws3->getStyle("D{$lastRow3}")->getNumberFormat()->setFormatCode('#,##0');
        $ws3->getRowDimension($lastRow3)->setRowHeight(22);

        $ws3->getColumnDimension('A')->setWidth(5);
        $ws3->getColumnDimension('B')->setWidth(22);
        $ws3->getColumnDimension('C')->setWidth(25);
        $ws3->getColumnDimension('D')->setWidth(18);
        $ws3->getColumnDimension('E')->setWidth(16);
        $ws3->getColumnDimension('F')->setWidth(18);
        $ws3->getColumnDimension('G')->setWidth(13);

        // ── Output ────────────────────────────────────────────
        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'hasil-penjualan-' . $now->format('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}