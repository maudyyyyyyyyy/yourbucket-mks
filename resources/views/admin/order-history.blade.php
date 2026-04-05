@extends('layouts.layout-admin')

@section('title', 'Orders History')
@section('header_title', 'Orders History')
@section('header_subtitle', 'Manage your orders history')

@section('content')
<div class="container py-4">
    <div class="card">
        <div class="card-header">
            <h4>Order History</h4>
        </div>

        <div class="card-body">

            <!-- Search Form -->
            <form action="{{ route('admin.history.index') }}" method="GET" class="row mb-4 g-2">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control"
                        placeholder="Search order..." value="{{ request('search') }}">
                </div>

                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="paid"       {{ request('status') == 'paid'       ? 'selected' : '' }}>💳 Paid</option>
                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>⚙️ Processing</option>
                        <option value="shipped"    {{ request('status') == 'shipped'    ? 'selected' : '' }}>🚚 Shipped</option>
                        <option value="delivered"  {{ request('status') == 'delivered'  ? 'selected' : '' }}>📦 Delivered</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <select name="shipping_type" class="form-select">
                        <option value="">All Shipping</option>
                        <option value="standard" {{ request('shipping_type') == 'standard' ? 'selected' : '' }}>🚚 Standard</option>
                        <option value="instant"  {{ request('shipping_type') == 'instant'  ? 'selected' : '' }}>⚡ Instant</option>
                        <option value="pickup"   {{ request('shipping_type') == 'pickup'   ? 'selected' : '' }}>🏪 Pickup</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">Search</button>
                    @if (request('search') || request('status') || request('shipping_type'))
                        <a href="{{ route('admin.history.index') }}" class="btn btn-secondary">Clear</a>
                    @endif
                </div>
            </form>

            @php
                $statusEmojis = [
                    'paid'       => '💳',
                    'processing' => '⚙️',
                    'shipped'    => '🚚',
                    'delivered'  => '📦',
                ];
                $statusBadge = [
                    'paid'       => 'info',
                    'processing' => 'primary',
                    'shipped'    => 'dark',
                    'delivered'  => 'success',
                ];
                $shippingLabels = [
                    'standard' => ['icon' => '🚚', 'label' => 'Standard', 'badge' => 'secondary'],
                    'instant'  => ['icon' => '⚡', 'label' => 'Instant',  'badge' => 'warning'],
                    'pickup'   => ['icon' => '🏪', 'label' => 'Pickup',   'badge' => 'info'],
                ];
            @endphp

            <!-- Orders Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Order Code</th>
                            <th>Customer</th>
                            <th>Pengiriman</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        @php
                            $ship = $shippingLabels[$order->shipping_type] ?? ['icon' => '❓', 'label' => ucfirst($order->shipping_type ?? '-'), 'badge' => 'secondary'];
                        @endphp
                        <tr>
                            <td>{{ $order->order_code }}</td>
                            <td>{{ $order->user->name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $ship['badge'] }}">
                                    {{ $ship['icon'] }} {{ $ship['label'] }}
                                </span>
                            </td>
                            <td>{{ $order->created_at->format('d M Y H:i') }}</td>
                            <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge bg-{{ $statusBadge[$order->status] ?? 'secondary' }}">
                                    {{ $statusEmojis[$order->status] ?? '' }}
                                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info"
                                    data-bs-toggle="modal"
                                    data-bs-target="#orderModal{{ $order->id }}">
                                    <i class="bi bi-eye"></i> Detail
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">No orders found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-end">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</div>

<!-- ORDER DETAIL MODALS -->
@foreach ($orders as $order)
@php
    $ship = $shippingLabels[$order->shipping_type] ?? ['icon' => '❓', 'label' => ucfirst($order->shipping_type ?? '-'), 'badge' => 'secondary'];
    $shippingCost = \App\Models\Order::getShippingCost($order->shipping_type ?? 'standard');
@endphp
<div class="modal fade" id="orderModal{{ $order->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">Order #{{ $order->order_code }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <!-- STATUS + DATE -->
                <div class="card border-0 bg-light mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">Order Status</p>
                                <span class="badge bg-{{ $statusBadge[$order->status] ?? 'secondary' }} fs-6">
                                    {{ $statusEmojis[$order->status] ?? '' }}
                                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                </span>
                            </div>
                            <div class="text-end">
                                <p class="text-muted mb-1">Order Date</p>
                                <h6>{{ $order->created_at->format('d M Y H:i') }}</h6>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CUSTOMER + PENGIRIMAN -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-2">👤 Info Customer</h6>
                        <p class="mb-1"><strong>Nama:</strong> {{ $order->user->name ?? '-' }}</p>
                        <p class="mb-1"><strong>Email:</strong> {{ $order->user->email ?? '-' }}</p>
                        <p class="mb-0"><strong>Payment:</strong> {{ $order->midtrans_payment_type ?? '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-2">🚚 Info Pengiriman</h6>
                        <div class="p-3 rounded border
                            {{ $order->shipping_type === 'pickup' ? 'bg-warning bg-opacity-10 border-warning' : 'bg-light' }}">
                            <p class="mb-1">
                                <strong>Jenis:</strong>
                                <span class="badge bg-{{ $ship['badge'] }} ms-1">
                                    {{ $ship['icon'] }} {{ $ship['label'] }}
                                </span>
                            </p>
                            <p class="mb-1">
                                <strong>Biaya:</strong>
                                @if($shippingCost === 0)
                                    <span class="text-success fw-semibold">Gratis</span>
                                @else
                                    Rp {{ number_format($shippingCost, 0, ',', '.') }}
                                @endif
                            </p>
                            @if($order->shipping_type === 'pickup')
                                <p class="mb-1"><strong>Alamat Toko:</strong> {{ config('app.store_address', 'Samping Gerbang BTP') }}</p>
                                <p class="mb-0"><strong>Jam:</strong> {{ config('app.store_hours', 'Senin – Minggu, 09.00 – 21.00') }}</p>
                            @else
                                <p class="mb-1"><strong>Alamat:</strong> {{ $order->shipping_address }}</p>
                                <p class="mb-0"><strong>Resi:</strong> {{ $order->resi_code ?? '-' }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                @if($order->notes)
                <div class="mb-4">
                    <h6 class="fw-bold mb-1">📝 Catatan</h6>
                    <p class="text-muted mb-0">{{ $order->notes }}</p>
                </div>
                @endif

                <!-- ORDER ITEMS -->
                <h6 class="fw-bold mb-2">🛍️ Produk yang Dipesan</h6>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th class="text-end">Price</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $subtotal = 0; @endphp
                            @foreach ($order->items as $item)
                            @php
                                $itemTotal = $item->price * $item->quantity;
                                $subtotal += $itemTotal;
                            @endphp
                            <tr>
                                <td>{{ $item->product->name ?? 'Deleted Product' }}</td>
                                <td class="text-end">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">Rp {{ number_format($itemTotal, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="3" class="text-end">Subtotal Produk</td>
                                <td class="text-end">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end">
                                    Biaya Pengiriman ({{ $ship['icon'] }} {{ $ship['label'] }})
                                </td>
                                <td class="text-end">
                                    @if($shippingCost === 0)
                                        <span class="text-success">Gratis</span>
                                    @else
                                        Rp {{ number_format($shippingCost, 0, ',', '.') }}
                                    @endif
                                </td>
                            </tr>
                            <tr class="fw-bold">
                                <td colspan="3" class="text-end">Total</td>
                                <td class="text-end">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>
@endforeach

@endsection