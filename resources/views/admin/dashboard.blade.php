@extends('layouts.layout-admin')

@section('title', 'Dashboard')
@section('header_title', 'Dashboard Overview')
@section('header_subtitle', 'Monitor your store performance')

@section('content')
<style>
    .dash-wrap { padding: 1.5rem; background: #f8f9fc; min-height: 100vh; }

    /* Greeting */
    .dash-greeting { margin-bottom: 1.5rem; }
    .dash-greeting h1 { font-size: 1.4rem; font-weight: 800; color: #1e1b4b; margin: 0; }
    .dash-greeting p  { color: #6b7280; font-size: 0.83rem; margin: 0.2rem 0 0; }
    .live-badge {
        display: inline-flex; align-items: center; gap: 5px;
        background: #ecfdf5; color: #059669;
        font-size: 0.65rem; font-weight: 700;
        padding: 2px 9px; border-radius: 20px;
        border: 1px solid #a7f3d0;
        text-transform: uppercase; letter-spacing: 0.06em;
        margin-left: 0.5rem; vertical-align: middle;
    }
    .live-dot {
        width: 6px; height: 6px; background: #10b981;
        border-radius: 50%; animation: blink 1.5s infinite;
    }
    @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.3} }

    /* Stat Grid */
    .stat-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 1rem; margin-bottom: 1.25rem; }
    @media(max-width:1100px){ .stat-grid{ grid-template-columns:repeat(2,1fr); } }
    @media(max-width:560px) { .stat-grid{ grid-template-columns:1fr; } }

    .stat-card {
        background: #fff; border-radius: 14px;
        padding: 1.2rem 1.25rem;
        border: 1px solid #e5e7eb;
        border-top: 3px solid transparent;
        transition: transform .2s, box-shadow .2s;
    }
    .stat-card:hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(0,0,0,.07); }
    .stat-card.c-purple { border-top-color: #7c3aed; }
    .stat-card.c-blue   { border-top-color: #2563eb; }
    .stat-card.c-green  { border-top-color: #059669; }
    .stat-card.c-amber  { border-top-color: #d97706; }

    .stat-icon {
        width: 38px; height: 38px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; margin-bottom: 0.85rem;
    }
    .c-purple .stat-icon { background: #f3e8ff; color: #7c3aed; }
    .c-blue   .stat-icon { background: #eff6ff; color: #2563eb; }
    .c-green  .stat-icon { background: #ecfdf5; color: #059669; }
    .c-amber  .stat-icon { background: #fffbeb; color: #d97706; }

    .stat-label { font-size: 0.7rem; color: #9ca3af; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 0.2rem; }
    .stat-value { font-size: 1.5rem; font-weight: 800; color: #111827; line-height: 1.1; margin-bottom: 0.4rem; }
    .stat-badge {
        display: inline-flex; align-items: center; gap: 2px;
        font-size: 0.7rem; font-weight: 600;
        padding: 2px 8px; border-radius: 10px;
    }
    .stat-badge.up   { background: #ecfdf5; color: #059669; }
    .stat-badge.down { background: #fef2f2; color: #dc2626; }
    .stat-badge.neu  { background: #f3f4f6; color: #6b7280; }

    .stat-mini { display: flex; gap: 0.4rem; margin-top: 0.7rem; }
    .stat-mini-item {
        flex: 1; background: #f9fafb; border-radius: 8px;
        padding: 0.35rem 0.4rem; text-align: center;
    }
    .stat-mini-item span   { display: block; font-size: 0.62rem; color: #9ca3af; }
    .stat-mini-item strong { font-size: 0.88rem; color: #111827; font-weight: 700; }

    .stat-progress { margin-top: 0.75rem; }
    .stat-progress-bar { height: 4px; background: #f3f4f6; border-radius: 10px; overflow: hidden; }
    .stat-progress-fill { height: 100%; border-radius: 10px; background: #7c3aed; }
    .stat-progress p { font-size: 0.68rem; color: #9ca3af; margin: 0.25rem 0 0; }

    /* Chart */
    .chart-card { background: #fff; border-radius: 14px; border: 1px solid #e5e7eb; padding: 1.3rem; }
    .chart-head {
        display: flex; align-items: flex-start; justify-content: space-between;
        margin-bottom: 1rem; flex-wrap: wrap; gap: 0.6rem;
    }
    .chart-title { font-size: 0.95rem; font-weight: 700; color: #111827; margin: 0; }
    .chart-sub   { font-size: 0.75rem; color: #9ca3af; margin: 0.1rem 0 0; }
    .chart-ctrls { display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap; }

    .p-btn {
        font-size: 0.73rem; font-weight: 600;
        padding: 5px 13px; border-radius: 20px;
        border: 1px solid #e5e7eb; background: #fff; color: #6b7280;
        cursor: pointer; transition: all .15s;
    }
    .p-btn:hover, .p-btn.active { background: #7c3aed; color: #fff; border-color: #7c3aed; }

    .sales-link {
        font-size: 0.73rem; font-weight: 600;
        padding: 5px 13px; border-radius: 20px;
        background: #f3e8ff; color: #7c3aed;
        border: 1px solid #ddd6fe; text-decoration: none;
        transition: all .15s;
    }
    .sales-link:hover { background: #7c3aed; color: #fff; }
</style>

<div class="dash-wrap">

    {{-- Greeting --}}
    <div class="dash-greeting">
        <h1>
            Selamat Datang, {{ explode(' ', Auth::user()->name)[0] }} 👋
        </h1>
        <p>{{ now()->translatedFormat('l, d F Y') }} — Pantau performa toko hari ini.</p>
    </div>

    {{-- Stats --}}
    <div class="stat-grid">

        {{-- Sales --}}
        <div class="stat-card c-purple">
            <div class="stat-icon"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="stat-label">Penjualan Bulan Ini</div>
            <div class="stat-value">Rp {{ number_format($totalSalesThisMonth, 0, ',', '.') }}</div>
            <span class="stat-badge {{ $salesGrowth >= 0 ? 'up' : 'down' }}">
                <i class="bi bi-arrow-{{ $salesGrowth >= 0 ? 'up' : 'down' }}-short"></i>
                {{ number_format(abs($salesGrowth), 1) }}% vs bulan lalu
            </span>
            <div class="stat-progress">
                <div class="stat-progress-bar">
                    <div class="stat-progress-fill" style="width:{{ min($salesTargetProgress, 100) }}%"></div>
                </div>
                <p>{{ number_format($salesTargetProgress, 1) }}% dari target</p>
            </div>
        </div>

        {{-- Orders --}}
        <div class="stat-card c-blue">
            <div class="stat-icon"><i class="bi bi-bag-check-fill"></i></div>
            <div class="stat-label">Total Orders</div>
            <div class="stat-value">{{ number_format($totalOrders) }}</div>
            <span class="stat-badge {{ $ordersGrowth >= 0 ? 'up' : 'down' }}">
                <i class="bi bi-arrow-{{ $ordersGrowth >= 0 ? 'up' : 'down' }}-short"></i>
                {{ number_format(abs($ordersGrowth), 1) }}% vs bulan lalu
            </span>
            <div class="stat-mini">
                <div class="stat-mini-item">
                    <span>Paid</span>
                    <strong>{{ $orderStats['paid'] ?? $orderStats['pending'] ?? 0 }}</strong>
                </div>
                <div class="stat-mini-item">
                    <span>Proses</span>
                    <strong>{{ $orderStats['processing'] }}</strong>
                </div>
                <div class="stat-mini-item">
                    <span>Selesai</span>
                    <strong>{{ $orderStats['completed'] }}</strong>
                </div>
            </div>
        </div>

        {{-- Products --}}
        <div class="stat-card c-green">
            <div class="stat-icon"><i class="bi bi-box-seam-fill"></i></div>
            <div class="stat-label">Produk</div>
            <div class="stat-value">{{ number_format($productsStats['total']) }}</div>
            <span class="stat-badge neu">+{{ $productsStats['newThisMonth'] }} baru bulan ini</span>
            <div class="stat-mini">
                <div class="stat-mini-item">
                    <span>In Stock</span>
                    <strong>{{ number_format($productsStats['inStock']) }}</strong>
                </div>
                <div class="stat-mini-item">
                    <span>Low Stock</span>
                    <strong style="color:#ef4444">{{ number_format($productsStats['lowStock']) }}</strong>
                </div>
            </div>
        </div>

        {{-- Customers --}}
        <div class="stat-card c-amber">
            <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
            <div class="stat-label">Pelanggan</div>
            <div class="stat-value">{{ number_format($customersStats['total']) }}</div>
            <span class="stat-badge {{ $customersGrowth >= 0 ? 'up' : 'down' }}">
                <i class="bi bi-arrow-{{ $customersGrowth >= 0 ? 'up' : 'down' }}-short"></i>
                {{ number_format(abs($customersGrowth), 1) }}% vs bulan lalu
            </span>
            <div class="stat-mini">
                <div class="stat-mini-item" style="flex:unset;width:100%">
                    <span>Aktif bulan ini</span>
                    <strong>{{ number_format($customersStats['activeThisMonth']) }}</strong>
                </div>
            </div>
        </div>

    </div>

    {{-- Chart --}}
    <div class="chart-card">
        <div class="chart-head">
            <div>
                <p class="chart-title">📈 Grafik Penjualan</p>
                <p class="chart-sub">Ringkasan pendapatan berdasarkan periode yang dipilih</p>
            </div>
            <div class="chart-ctrls">
                <button class="p-btn active" data-period="7">7 Hari</button>
                <button class="p-btn" data-period="30">30 Hari</button>
                <button class="p-btn" data-period="365">Tahun Ini</button>
                <a href="{{ route('admin.sales') }}" class="sales-link">📊 Hasil Penjualan →</a>
            </div>
        </div>
        <div id="salesChart" style="min-height:300px"></div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.45.1/apexcharts.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chart = new ApexCharts(document.querySelector('#salesChart'), {
        series: [{ name: 'Penjualan', data: @json($salesChart['data']) }],
        chart: {
            height: 300, type: 'area',
            toolbar: { show: false }, zoom: { enabled: false },
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2.5 },
        xaxis: {
            categories: @json($salesChart['labels']),
            axisBorder: { show: false }, axisTicks: { show: false },
            labels: { style: { colors: '#9ca3af', fontSize: '12px' } }
        },
        yaxis: {
            labels: {
                formatter: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v),
                style: { colors: '#9ca3af', fontSize: '11px' }
            }
        },
        tooltip: {
            theme: 'dark',
            y: { formatter: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v) }
        },
        colors: ['#7c3aed'],
        fill: {
            type: 'gradient',
            gradient: { shade: 'light', type: 'vertical', opacityFrom: 0.2, opacityTo: 0.01 }
        },
        grid: { borderColor: '#f3f4f6', strokeDashArray: 4 },
        markers: { size: 4, colors: ['#7c3aed'], strokeWidth: 0, hover: { size: 6 } }
    });
    chart.render();

    document.querySelectorAll('.p-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.p-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            fetch(`/admin/dashboard/sales-chart?period=${this.dataset.period}`)
                .then(r => r.json())
                .then(d => {
                    chart.updateOptions({ xaxis: { categories: d.labels } });
                    chart.updateSeries([{ data: d.data }]);
                });
        });
    });
});
</script>
@endpush