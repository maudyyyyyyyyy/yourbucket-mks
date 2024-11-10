@extends('layouts.layout-admin')

@section('title', 'Dashboard')
@section('header_title', 'Dashboard Overview')
@section('header_subtitle', 'Monitor your store performance')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
            <!-- Sales Card -->
            <div class="bg-white rounded-lg p-4 sm:p-6 shadow hover:shadow-md transition">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-emerald-50 rounded">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                </path>
                            </svg>
                        </div>
                        <h3 class="text-gray-600 text-sm font-medium">Sales</h3>
                    </div>
                    <span
                        class="bg-emerald-50 text-emerald-600 px-2 py-1 rounded text-xs">+{{ number_format($salesGrowth, 1) }}%</span>
                </div>
                <p class="text-xl sm:text-2xl font-bold">Rp {{ number_format($totalSalesThisMonth, 0, ',', '.') }}</p>
                <div class="mt-2 h-1.5 bg-gray-100 rounded-full">
                    <div class="h-full bg-emerald-500 rounded-full" style="width: {{ min($salesTargetProgress, 100) }}%">
                    </div>
                </div>
                <p class="mt-1 text-xs text-gray-500">{{ number_format($salesTargetProgress, 1) }}% of target</p>
            </div>

            <!-- Orders Card -->
            <div class="bg-white rounded-lg p-4 sm:p-6 shadow hover:shadow-md transition">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-blue-50 rounded">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                        </div>
                        <h3 class="text-gray-600 text-sm font-medium">Orders</h3>
                    </div>
                    <span
                        class="bg-blue-50 text-blue-600 px-2 py-1 rounded text-xs">+{{ number_format($ordersGrowth, 1) }}%</span>
                </div>
                <p class="text-xl sm:text-2xl font-bold">{{ number_format($totalOrders) }}</p>
                <div class="mt-2 grid grid-cols-3 gap-2">
                    <div class="text-center p-2 bg-gray-50 rounded">
                        <p class="text-[10px] sm:text-xs text-gray-500">Pending</p>
                        <p class="text-sm sm:text-base font-medium">{{ $orderStats['pending'] }}</p>
                    </div>
                    <div class="text-center p-2 bg-gray-50 rounded">
                        <p class="text-[10px] sm:text-xs text-gray-500">Processing</p>
                        <p class="text-sm sm:text-base font-medium">{{ $orderStats['processing'] }}</p>
                    </div>
                    <div class="text-center p-2 bg-gray-50 rounded">
                        <p class="text-[10px] sm:text-xs text-gray-500">Completed</p>
                        <p class="text-sm sm:text-base font-medium">{{ $orderStats['completed'] }}</p>
                    </div>
                </div>
            </div>
            <!-- Products Card -->
            <div class="bg-white rounded-lg p-4 sm:p-6 shadow hover:shadow-md transition">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-purple-50 rounded">
                            <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <h3 class="text-gray-600 text-sm font-medium">Products</h3>
                    </div>
                    <span
                        class="bg-purple-50 text-purple-600 px-2 py-1 rounded text-xs">+{{ $productsStats['newThisMonth'] }}</span>
                </div>
                <p class="text-xl sm:text-2xl font-bold">{{ number_format($productsStats['total']) }}</p>
                <div class="mt-2 grid grid-cols-2 gap-2">
                    <div class="text-center p-2 bg-gray-50 rounded">
                        <p class="text-[10px] sm:text-xs text-gray-500">In Stock</p>
                        <p class="text-sm sm:text-base font-medium">{{ number_format($productsStats['inStock']) }}</p>
                    </div>
                    <div class="text-center p-2 bg-gray-50 rounded">
                        <p class="text-[10px] sm:text-xs text-gray-500">Low Stock</p>
                        <p class="text-sm sm:text-base font-medium text-red-500">
                            {{ number_format($productsStats['lowStock']) }}</p>
                    </div>
                </div>
            </div>

            <!-- Customers Card -->
            <div class="bg-white rounded-lg p-4 sm:p-6 shadow hover:shadow-md transition">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-amber-50 rounded">
                            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                        </div>
                        <h3 class="text-gray-600 text-sm font-medium">Customers</h3>
                    </div>
                    <span
                        class="bg-amber-50 text-amber-600 px-2 py-1 rounded text-xs">+{{ number_format($customersGrowth, 1) }}%</span>
                </div>
                <p class="text-xl sm:text-2xl font-bold">{{ number_format($customersStats['total']) }}</p>
                <div class="mt-2 p-2 bg-gray-50 rounded">
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] sm:text-xs text-gray-500">Active this month</span>
                        <span
                            class="text-sm sm:text-base font-medium">{{ number_format($customersStats['activeThisMonth']) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Tables Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
            <!-- Sales Chart -->
            <div class="bg-white rounded-lg p-4 sm:p-6 shadow-sm border border-gray-100">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 gap-2">
                    <div>
                        <h3 class="text-base font-semibold text-gray-800">Sales Overview</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Summary of sales for selected period</p>
                    </div>
                    <select
                        class="text-xs bg-gray-50 border border-gray-200 rounded px-3 py-1.5 hover:border-gray-300 focus:outline-none focus:ring-1 focus:ring-blue-500/20 transition-all cursor-pointer"
                        id="salesChartPeriod">
                        <option value="7">Last 7 Days</option>
                        <option value="30">Last 30 Days</option>
                        <option value="365">This Year</option>
                    </select>
                </div>
                <div id="salesChart" class="w-full h-[250px] sm:h-[300px]"></div>
            </div>
            <!-- Top Products -->
            <div class="bg-white rounded-lg p-4 sm:p-6 shadow-sm border border-gray-100">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 gap-2">
                    <h3 class="text-base font-semibold text-gray-800">Top Products</h3>
                    <select
                        class="text-xs bg-gray-50 border border-gray-200 rounded px-3 py-1.5 hover:border-gray-300 focus:outline-none focus:ring-1 focus:ring-blue-500/20 transition-all cursor-pointer"
                        id="topProductsFilter">
                        <option value="revenue">By Revenue</option>
                        <option value="quantity">By Quantity</option>
                    </select>
                </div>
                <div class="space-y-2 max-h-[250px] sm:max-h-[300px] overflow-y-auto">
                    @forelse($topProducts as $product)
                        <div class="p-2 bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-150">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded bg-gray-200 flex-shrink-0">
                                    @if ($product->image)
                                        <img src="{{ $product->image }}" alt="{{ $product->name }}"
                                            class="w-full h-full object-cover rounded">
                                    @else
                                        <svg class="w-4 h-4 m-2 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                    @endif
                                </div>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-medium text-gray-800">{{ $product->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $product->total_sold }} sold</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-800">
                                        Rp {{ number_format($product->revenue, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-gray-500 text-sm">
                            No products data available
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Orders Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100">
            <div class="p-4 border-b border-gray-100">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <h3 class="text-base font-semibold text-gray-800">Recent Orders</h3>
                </div>
            </div>
            <div class="overflow-x-auto">
                <div class="min-w-full inline-block align-middle">
                    <div class="overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th scope="col"
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Order ID
                                    </th>
                                    <th scope="col"
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Customer
                                    </th>
                                    <th scope="col"
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">
                                        Items
                                    </th>
                                    <th scope="col"
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Total
                                    </th>
                                    <th scope="col"
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col"
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">
                                        Date
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($recentOrders as $order)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                            #{{ $order->id }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                            {{ $order->user?->name }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell">
                                            {{ $order->items->count() }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                            Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span
                                                class="px-2 py-1 text-xs rounded-full {{ match ($order->status) {
                                                    'delivered', 'completed' => 'bg-green-50 text-green-600',
                                                    'pending' => 'bg-yellow-50 text-yellow-600',
                                                    'processing' => 'bg-blue-50 text-blue-600',
                                                    'shipped' => 'bg-indigo-50 text-indigo-600',
                                                    'cancelled' => 'bg-red-50 text-red-600',
                                                    default => 'bg-gray-50 text-gray-600',
                                                } }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell">
                                            {{ $order->created_at->format('d M Y') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-3 text-center text-gray-500 text-sm">
                                            No orders found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.45.1/apexcharts.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const options = {
                series: [{
                    name: 'Sales',
                    data: @json($salesChart['data'])
                }],
                chart: {
                    height: '100%',
                    type: 'area',
                    toolbar: {
                        show: false
                    },
                    zoom: {
                        enabled: false
                    },
                    fontFamily: 'Inter, sans-serif',
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 3,
                    lineCap: 'round'
                },
                xaxis: {
                    categories: @json($salesChart['labels']),
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
                    labels: {
                        style: {
                            fontSize: '12px',
                            fontWeight: 500,
                            colors: '#64748b'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        formatter: function(value) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                        },
                        style: {
                            fontSize: '12px',
                            fontWeight: 500,
                            colors: '#64748b'
                        }
                    }
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function(value) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                },
                colors: ['#0EA5E9'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shade: 'dark',
                        type: 'vertical',
                        shadeIntensity: 0.5,
                        opacityFrom: 0.3,
                        opacityTo: 0.1,
                        stops: [0, 100]
                    }
                },
                grid: {
                    borderColor: '#e2e8f0',
                    strokeDashArray: 4,
                    padding: {
                        top: 0,
                        right: 0,
                        bottom: 0,
                        left: 10
                    }
                }
            };

            const chart = new ApexCharts(document.querySelector("#salesChart"), options);
            chart.render();

            // Handle period change
            document.getElementById('salesChartPeriod').addEventListener('change', function() {
                fetch(`/admin/dashboard/sales-chart?period=${this.value}`)
                    .then(response => response.json())
                    .then(data => {
                        chart.updateOptions({
                            xaxis: {
                                categories: data.labels
                            }
                        });
                        chart.updateSeries([{
                            data: data.data
                        }]);
                    });
            });
        });
    </script>

    <style>
        .apexcharts-tooltip {
            @apply shadow-lg border-0 rounded-lg !important;
        }

        .apexcharts-tooltip-title {
            @apply border-b border-gray-700 bg-gray-800 px-4 py-2 rounded-t-lg !important;
        }

        .apexcharts-tooltip-series-group {
            @apply px-4 py-2 !important;
        }

        .apexcharts-tooltip.apexcharts-theme-dark {
            @apply bg-gray-800 !important;
        }

        @media (max-width: 640px) {
            .apexcharts-canvas {
                padding: 0 !important;
            }

            .apexcharts-title-text {
                font-size: 14px !important;
            }

            .apexcharts-legend-text {
                font-size: 12px !important;
            }

            .apexcharts-text tspan {
                font-size: 12px !important;
            }
        }
    </style>
@endpush
