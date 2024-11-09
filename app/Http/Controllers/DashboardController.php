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
    public function index()
    {
        // Total Sales Statistics
        $totalSalesThisMonth = Order::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->where('status', 'paid')
            ->sum('total_amount');

        $totalSalesLastMonth = Order::whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->where('status', 'paid')
            ->sum('total_amount');

        $salesGrowth = $totalSalesLastMonth > 0
            ? (($totalSalesThisMonth - $totalSalesLastMonth) / $totalSalesLastMonth) * 100
            : 100;

        $monthlyTarget = 60000000;
        $salesTargetProgress = ($totalSalesThisMonth / $monthlyTarget) * 100;

        // Orders Statistics
        $totalOrders = Order::count();
        $ordersThisMonth = Order::whereMonth('created_at', Carbon::now()->month)->count();
        $ordersLastMonth = Order::whereMonth('created_at', Carbon::now()->subMonth()->month)->count();
        $ordersGrowth = $ordersLastMonth > 0
            ? (($ordersThisMonth - $ordersLastMonth) / $ordersLastMonth) * 100
            : 100;

        $orderStats = [
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'completed' => Order::whereIn('status', ['delivered', 'paid'])->count(),
        ];

        // Products Statistics
        $productsStats = [
            'total' => Product::count(),
            'inStock' => Product::where('stock', '>', 0)->count(),
            'lowStock' => Product::where('stock', '<=', 5)
                ->where('stock', '>', 0)
                ->count(),
            'newThisMonth' => Product::whereMonth('created_at', Carbon::now()->month)->count(),
        ];

        // Customers Statistics
        $customersStats = [
            'total' => User::where('role', 'user')->count(),
            'activeThisMonth' => User::where('role', 'user')
                ->whereHas('orders', function ($query) {
                    $query->whereMonth('created_at', Carbon::now()->month);
                })
                ->count(),
            'newThisMonth' => User::where('role', 'user')
                ->whereMonth('created_at', Carbon::now()->month)
                ->count(),
        ];

        $customersGrowth = User::where('role', 'user')
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->count();
        $customersGrowth = $customersGrowth > 0
            ? (($customersStats['newThisMonth'] - $customersGrowth) / $customersGrowth) * 100
            : 100;

        // Sales Chart Data (Last 7 days)
        $salesData = Order::where('status', 'paid')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('date')
            ->select(
                DB::raw('Date(created_at) as date'),
                DB::raw('SUM(total_amount) as total')
            )
            ->get();

        // Restructure sales chart data
        $salesChart = [
            'labels' => [],
            'data' => []
        ];

        foreach ($salesData as $data) {
            $salesChart['labels'][] = Carbon::parse($data->date)->format('D');
            $salesChart['data'][] = $data->total;
        }

        // Top Products
        $topProducts = Product::withCount([
            'orderItems as total_sold' => function ($query) {
                $query->whereHas('order', function ($q) {
                    $q->where('status', 'paid');
                });
            }
        ])
            ->withSum([
                'orderItems as revenue' => function ($query) {
                    $query->whereHas('order', function ($q) {
                        $q->where('status', 'paid');
                    });
                }
            ], 'price')
            ->orderByDesc('revenue')
            ->take(5)
            ->get();

        // Recent Orders
        $recentOrders = Order::with(['user', 'items'])
            ->select('id', 'user_id', 'total_amount', 'status', 'created_at')
            ->latest()
            ->take(5)
            ->get();
        return view('admin.dashboard', compact(
            'totalSalesThisMonth',
            'salesGrowth',
            'salesTargetProgress',
            'totalOrders',
            'ordersGrowth',
            'orderStats',
            'productsStats',
            'customersStats',
            'customersGrowth',
            'salesChart',
            'topProducts',
            'recentOrders'
        ));
    }
}