<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private const PAID_STATUSES = [
        'paid',
        'settlement',
        'capture',
        'completed',
        'delivered'
    ];

    public function index()
    {
        $now       = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();

        $totalSalesThisMonth = Order::whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->whereIn('status', self::PAID_STATUSES)
            ->sum('total_amount');

        $totalSalesLastMonth = Order::whereMonth('created_at', $lastMonth->month)
            ->whereYear('created_at', $lastMonth->year)
            ->whereIn('status', self::PAID_STATUSES)
            ->sum('total_amount');

        $salesGrowth = $totalSalesLastMonth > 0
            ? (($totalSalesThisMonth - $totalSalesLastMonth) / $totalSalesLastMonth) * 100
            : ($totalSalesThisMonth > 0 ? 100 : 0);

        $monthlyTarget      = 60000000;
        $salesTargetProgress = $monthlyTarget > 0
            ? ($totalSalesThisMonth / $monthlyTarget) * 100
            : 0;

        $totalOrders      = Order::count();
        $ordersThisMonth  = Order::whereMonth('created_at', $now->month)->count();
        $ordersLastMonth  = Order::whereMonth('created_at', $lastMonth->month)->count();

        $ordersGrowth = $ordersLastMonth > 0
            ? (($ordersThisMonth - $ordersLastMonth) / $ordersLastMonth) * 100
            : ($ordersThisMonth > 0 ? 100 : 0);

        $orderStats = [
            'pending'    => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'completed'  => Order::whereIn('status', self::PAID_STATUSES)->count(),
        ];

        $productsStats = [
            'total'        => Product::count(),
            'inStock'      => Product::where('stock', '>', 0)->count(),
            'lowStock'     => Product::where('stock', '<=', 5)->where('stock', '>', 0)->count(),
            'newThisMonth' => Product::whereMonth('created_at', $now->month)->count(),
        ];

        $customersStats = [
            'total'           => User::where('role', 'user')->count(),
            'activeThisMonth' => User::where('role', 'user')
                ->whereHas('orders', function ($query) use ($now) {
                    $query->whereMonth('created_at', $now->month);
                })->count(),
            'newThisMonth'    => User::where('role', 'user')
                ->whereMonth('created_at', $now->month)->count(),
        ];

        $lastMonthNewCustomers = User::where('role', 'user')
            ->whereMonth('created_at', $lastMonth->month)->count();

        $customersGrowth = $lastMonthNewCustomers > 0
            ? (($customersStats['newThisMonth'] - $lastMonthNewCustomers) / $lastMonthNewCustomers) * 100
            : ($customersStats['newThisMonth'] > 0 ? 100 : 0);

        $salesData = Order::whereIn('status', self::PAID_STATUSES)
            ->where('created_at', '>=', $now->copy()->subDays(6)->startOfDay())
            ->groupBy(DB::raw('DATE(created_at)'))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as total'))
            ->orderBy('date')
            ->get();

        $salesChart = ['labels' => [], 'data' => []];
        for ($i = 6; $i >= 0; $i--) {
            $date  = $now->copy()->subDays($i)->format('Y-m-d');
            $found = $salesData->firstWhere('date', $date);
            $salesChart['labels'][] = Carbon::parse($date)->format('D');
            $salesChart['data'][]   = $found ? $found->total : 0;
        }

        $topProducts = Product::withCount([
            'orderItems as total_sold' => fn($q) => $q->whereHas('order', fn($q) => $q->whereIn('status', self::PAID_STATUSES))
        ])->withSum([
            'orderItems as revenue' => fn($q) => $q->whereHas('order', fn($q) => $q->whereIn('status', self::PAID_STATUSES))
        ], 'price')
        ->orderByDesc('revenue')
        ->take(5)
        ->get();

        $recentOrders = Order::with(['user', 'items'])
            ->select('id', 'user_id', 'total_amount', 'status', 'created_at')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalSalesThisMonth', 'salesGrowth', 'salesTargetProgress',
            'totalOrders', 'ordersGrowth', 'orderStats',
            'productsStats', 'customersStats', 'customersGrowth',
            'salesChart', 'topProducts', 'recentOrders'
        ));
    }

    // -------------------------------------------------------
    // Halaman Hasil Penjualan
    // -------------------------------------------------------
    public function sales()
    {
        $now = Carbon::now();

        $totalSalesThisMonth = Order::whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->whereIn('status', self::PAID_STATUSES)
            ->sum('total_amount');

        $totalOrders = Order::count();

        $orderStats = [
            'paid'       => Order::where('status', 'paid')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'completed'  => Order::where('status', 'delivered')->count(),
        ];

        $topProducts = Product::withCount([
            'orderItems as total_sold' => fn($q) => $q->whereHas('order', fn($q) => $q->whereIn('status', self::PAID_STATUSES))
        ])->withSum([
            'orderItems as revenue' => fn($q) => $q->whereHas('order', fn($q) => $q->whereIn('status', self::PAID_STATUSES))
        ], 'price')
        ->orderByDesc('revenue')
        ->take(10)
        ->get();

        $recentOrders = Order::with(['user', 'items'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.sales', compact(
            'totalSalesThisMonth', 'totalOrders', 'orderStats',
            'topProducts', 'recentOrders'
        ));
    }

    // -------------------------------------------------------
    // AJAX: update chart berdasarkan period
    // -------------------------------------------------------
    public function salesChart(Request $request)
    {
        $period    = (int) $request->get('period', 7);
        $now       = Carbon::now();
        $startDate = $now->copy()->subDays($period - 1)->startOfDay();

        $salesData = Order::whereIn('status', self::PAID_STATUSES)
            ->where('created_at', '>=', $startDate)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as total'))
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels = [];
        $data   = [];

        for ($i = $period - 1; $i >= 0; $i--) {
            $date     = $now->copy()->subDays($i)->format('Y-m-d');
            $labels[] = $period <= 30
                ? Carbon::parse($date)->format('d M')
                : Carbon::parse($date)->format('M Y');
            $data[]   = isset($salesData[$date]) ? (float) $salesData[$date]->total : 0;
        }

        return response()->json(['labels' => $labels, 'data' => $data]);
    }
}