@extends('layouts.layout-admin')

@section('title', 'Hasil Penjualan')
@section('header_title', 'Hasil Penjualan')

@section('content')
<style>
    /* ── Wrapper ── */
    .sales-wrap {
        padding: 2rem;
        background: #f8f9fc;
        min-height: 100vh;
    }

    /* ── Header ── */
    .page-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        flex-wrap: wrap;
        gap: 10px;
    }

    .head-title h2 {
        font-size: 22px;
        font-weight: 700;
        color: #111827;
        margin: 0;
    }

    .head-title span {
        font-size: 13px;
        color: #6b7280;
    }

    .head-actions {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    /* ── Buttons ── */
    .back-btn {
        font-size: .78rem;
        font-weight: 600;
        padding: 7px 15px;
        border-radius: 20px;
        background: #fff;
        color: #6b7280;
        border: 1px solid #e5e7eb;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 5px;
        transition: .2s;
    }

    .back-btn:hover {
        background: #f3f4f6;
        color: #374151;
    }

    .export-btn {
        font-size: .78rem;
        font-weight: 700;
        padding: 7px 16px;
        border-radius: 20px;
        background: #059669;
        color: #fff;
        border: 1px solid #047857;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: .2s;
        cursor: pointer;
    }

    .export-btn:hover {
        background: #047857;
        box-shadow: 0 4px 12px rgba(5, 150, 105, .25);
    }

    .export-btn .spinner {
        display: none;
        width: 14px;
        height: 14px;
        border: 2px solid rgba(255, 255, 255, .4);
        border-top-color: #fff;
        border-radius: 50%;
        animation: spin .7s linear infinite;
    }

    .export-btn.loading .spinner { display: inline-block; }
    .export-btn.loading .icon    { display: none; }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* ── Summary Strip ── */
    .sum-strip {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    @media (max-width: 900px) { .sum-strip { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 480px) { .sum-strip { grid-template-columns: 1fr; } }

    .sum-card {
        background: #fff;
        border-radius: 14px;
        padding: 1rem 1.15rem;
        border: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        gap: .85rem;
        transition: .2s;
    }

    .sum-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 22px rgba(0, 0, 0, .06);
    }

    .sum-icon {
        width: 42px;
        height: 42px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
    }

    .sum-icon.purple { background: #f3e8ff; color: #7c3aed; }
    .sum-icon.blue   { background: #eff6ff; color: #2563eb; }
    .sum-icon.green  { background: #ecfdf5; color: #059669; }
    .sum-icon.amber  { background: #fffbeb; color: #d97706; }

    .sum-label {
        font-size: .68rem;
        color: #9ca3af;
        font-weight: 600;
        text-transform: uppercase;
    }

    .sum-value {
        font-size: 1.2rem;
        font-weight: 800;
        color: #111827;
    }

    /* ── Two Column Grid ── */
    .two-col {
        display: grid;
        grid-template-columns: 1fr 1.65fr;
        gap: 1rem;
    }

    @media (max-width: 900px) { .two-col { grid-template-columns: 1fr; } }

    /* ── Card ── */
    .s-card {
        background: #fff;
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        overflow: hidden;
    }

    .s-card-head {
        padding: 1rem 1.2rem;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .s-card-title {
        font-size: .88rem;
        font-weight: 700;
        color: #111827;
        margin: 0;
    }

    .s-card-sub {
        font-size: .7rem;
        color: #9ca3af;
        margin: .1rem 0 0;
    }

    .see-all {
        font-size: .75rem;
        font-weight: 600;
        color: #7c3aed;
        text-decoration: none;
    }

    .see-all:hover { text-decoration: underline; }

    /* ── Product List ── */
    .prod-list {
        padding: .75rem 1rem;
        display: flex;
        flex-direction: column;
        gap: .3rem;
        max-height: 460px;
        overflow-y: auto;
    }

    .prod-item {
        display: flex;
        align-items: center;
        gap: .7rem;
        padding: .55rem .6rem;
        border-radius: 10px;
        transition: .15s;
    }

    .prod-item:hover { background: #f9fafb; }

    .rank {
        width: 20px;
        text-align: center;
        font-size: .82rem;
        font-weight: 700;
        color: #d1d5db;
    }

    .prod-img {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: #f3f4f6;
        overflow: hidden;
    }

    .prod-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .prod-name {
        font-size: .8rem;
        font-weight: 600;
        color: #111827;
    }

    .prod-sold {
        font-size: .68rem;
        color: #9ca3af;
    }

    .prod-bar {
        height: 3px;
        background: #f3f4f6;
        border-radius: 10px;
        margin-top: .2rem;
    }

    .prod-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #7c3aed, #a78bfa);
    }

    /* ── Orders Table ── */
    .o-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .8rem;
    }

    .o-table thead th {
        padding: .6rem .9rem;
        text-align: left;
        font-size: .65rem;
        font-weight: 700;
        color: #9ca3af;
        text-transform: uppercase;
        border-bottom: 1px solid #f3f4f6;
        background: #fafafa;
    }

    .o-table tbody td {
        padding: .65rem .9rem;
        border-bottom: 1px solid #f9fafb;
        color: #374151;
    }

    .o-table tbody tr:hover td { background: #fafafa; }

    .o-code {
        font-weight: 800;
        color: #111827;
    }

    .o-badge {
        display: inline-flex;
        align-items: center;
        font-size: .68rem;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 20px;
    }

    .o-badge.paid       { background: #eff6ff; color: #1d4ed8; }
    .o-badge.processing { background: #fffbeb; color: #b45309; }
    .o-badge.shipped    { background: #f0fdf4; color: #166534; }
    .o-badge.delivered  { background: #ecfdf5; color: #059669; }
    .o-badge.def        { background: #f3f4f6; color: #6b7280; }

    /* ── Scrollbar ── */
    ::-webkit-scrollbar { width: 4px; }
    ::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
</style>

<div class="sales-wrap">

    {{-- Page Header --}}
    <div class="page-head">
        <div class="head-title">
            <h2>📊 Hasil Penjualan</h2>
            <span>Ringkasan performa penjualan toko</span>
        </div>
        <div class="head-actions">
            <a href="{{ route('admin.sales.export') }}" class="export-btn" id="exportBtn">
                <span class="icon">⬇️</span>
                <span class="spinner"></span>
                Download Excel
            </a>
            <a href="{{ route('admin.dashboard') }}" class="back-btn">
                <i class="bi bi-arrow-left"></i> Dashboard
            </a>
        </div>
    </div>

    {{-- Summary Strip --}}
    <div class="sum-strip">
        <div class="sum-card">
            <div class="sum-icon purple"><i class="bi bi-cash-stack"></i></div>
            <div>
                <div class="sum-label">Pendapatan Bulan Ini</div>
                <div class="sum-value">Rp {{ number_format($totalSalesThisMonth, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="sum-card">
            <div class="sum-icon blue"><i class="bi bi-bag-check-fill"></i></div>
            <div>
                <div class="sum-label">Total Orders</div>
                <div class="sum-value">{{ number_format($totalOrders) }}</div>
            </div>
        </div>
        <div class="sum-card">
            <div class="sum-icon green"><i class="bi bi-check2-circle"></i></div>
            <div>
                <div class="sum-label">Order Selesai</div>
                <div class="sum-value">{{ number_format($orderStats['completed']) }}</div>
            </div>
        </div>
        <div class="sum-card">
            <div class="sum-icon amber"><i class="bi bi-gear-fill"></i></div>
            <div>
                <div class="sum-label">Sedang Diproses</div>
                <div class="sum-value">{{ number_format($orderStats['processing']) }}</div>
            </div>
        </div>
    </div>

    {{-- Two Column --}}
    <div class="two-col">

        {{-- Top Products --}}
        <div class="s-card">
            <div class="s-card-head">
                <div>
                    <p class="s-card-title">🏆 Top Produk</p>
                    <p class="s-card-sub">Berdasarkan total pendapatan</p>
                </div>
            </div>

            @php $maxRev = $topProducts->max('revenue') ?: 1; @endphp

            <div class="prod-list">
                @forelse($topProducts as $i => $product)
                    <div class="prod-item">
                        <div class="rank">
                            {{ $i === 0 ? '🥇' : ($i === 1 ? '🥈' : ($i === 2 ? '🥉' : $i + 1)) }}
                        </div>
                        <div class="prod-img">
                            @if($product->image)
                                <img src="{{ $product->image }}" alt="{{ $product->name }}">
                            @else
                                <i class="bi bi-image"></i>
                            @endif
                        </div>
                        <div class="prod-info">
                            <div class="prod-name">{{ $product->name }}</div>
                            <div class="prod-sold">{{ number_format($product->total_sold) }} terjual</div>
                            <div class="prod-bar">
                                <div class="prod-bar-fill" style="width:{{ ($product->revenue / $maxRev) * 100 }}%"></div>
                            </div>
                        </div>
                        <div>
                            <strong>Rp {{ number_format($product->revenue ?? 0, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                @empty
                    <div style="text-align:center; padding:2.5rem; color:#9ca3af; font-size:.85rem;">
                        Belum ada data produk
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Recent Orders --}}
        <div class="s-card">
            <div class="s-card-head">
                <div>
                    <p class="s-card-title">🧾 Pesanan Terbaru</p>
                    <p class="s-card-sub">10 pesanan terakhir</p>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="see-all">Lihat Semua →</a>
            </div>

            <div style="overflow-x:auto;">
                <table class="o-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                            @php
                                $bc = match($order->status) {
                                    'paid'       => 'paid',
                                    'processing' => 'processing',
                                    'shipped'    => 'shipped',
                                    'delivered'  => 'delivered',
                                    default      => 'def',
                                };
                                $bl = match($order->status) {
                                    'paid'       => '💳 Paid',
                                    'processing' => '⚙️ Proses',
                                    'shipped'    => '🚚 Dikirim',
                                    'delivered'  => '📦 Selesai',
                                    default      => ucfirst($order->status),
                                };
                            @endphp
                            <tr>
                                <td><span class="o-code">#{{ $order->order_code ?? $order->id }}</span></td>
                                <td>{{ $order->user?->name ?? '-' }}</td>
                                <td style="font-weight:700; color:#111827; white-space:nowrap;">
                                    Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                </td>
                                <td><span class="o-badge {{ $bc }}">{{ $bl }}</span></td>
                                <td style="color:#9ca3af; white-space:nowrap; font-size:.75rem;">
                                    {{ $order->created_at->format('d M Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align:center; padding:2rem; color:#9ca3af;">
                                    Belum ada pesanan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>

<script>
    document.getElementById('exportBtn').addEventListener('click', function () {
        this.classList.add('loading');
        setTimeout(() => this.classList.remove('loading'), 3000);
    });
</script>

@endsection