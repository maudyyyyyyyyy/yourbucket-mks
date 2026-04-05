<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DailySalesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Order::select(
                DB::raw('DATE(created_at) as tanggal'),
                DB::raw('SUM(total) as total_sales')
            )
            ->where('status', '!=', 'cancelled')
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'DESC')
            ->get()
            ->map(function ($item) {
                return [
                    'Tanggal' => $item->tanggal,
                    'Total Sales' => 'Rp ' . number_format($item->total_sales, 0, ',', '.')
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Total Sales'
        ];
    }
}
