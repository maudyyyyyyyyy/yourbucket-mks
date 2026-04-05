@extends('layouts.layout-admin')

@section('title', 'Orders')
@section('header_title', 'Orders')
@section('header_subtitle', 'Manage your orders')

@section('content')
<div class="container-fluid">

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            ✅ {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            ❌ {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">

                @php
                    $statusEmojis = [
                        'paid'       => '💳',
                        'processing' => '⚙️',
                        'shipped'    => '🚚',
                        'delivered'  => '📦',
                    ];

                    $shippingLabels = [
                        'standard' => ['icon' => '🚚', 'label' => 'Standard', 'badge' => 'secondary'],
                        'instant'  => ['icon' => '⚡', 'label' => 'Instant',  'badge' => 'warning'],
                        'pickup'   => ['icon' => '🏪', 'label' => 'Pickup',   'badge' => 'info'],
                    ];
                @endphp

                {{-- SEARCH + FILTER --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <form action="{{ route('admin.orders.index') }}" method="GET"
                          class="d-flex align-items-center gap-2">

                        <div class="input-group">
                            <input type="text"
                                   name="search"
                                   class="form-control form-control-sm"
                                   placeholder="Search order..."
                                   value="{{ request('search') }}">

                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                @foreach (['paid', 'processing', 'shipped', 'delivered'] as $status)
                                    <option value="{{ $status }}"
                                        {{ request('status') == $status ? 'selected' : '' }}>
                                        {{ $statusEmojis[$status] }} {{ ucfirst(str_replace('_', ' ', $status)) }}
                                    </option>
                                @endforeach
                            </select>

                            <select name="shipping_type" class="form-select form-select-sm">
                                <option value="">All Shipping</option>
                                @foreach (['standard' => '🚚 Standard', 'instant' => '⚡ Instant', 'pickup' => '🏪 Pickup'] as $val => $lbl)
                                    <option value="{{ $val }}"
                                        {{ request('shipping_type') == $val ? 'selected' : '' }}>
                                        {{ $lbl }}
                                    </option>
                                @endforeach
                            </select>

                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>

                        @if (request('search') || request('status') || request('shipping_type'))
                            <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-secondary">
                                Clear
                            </a>
                        @endif
                    </form>
                </div>

                {{-- TABLE --}}
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Order Code</th>
                            <th>Customer</th>
                            <th>Pengiriman</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Date</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($orders as $order)
                        @php
                            $ship = $shippingLabels[$order->shipping_type] ?? ['icon' => '❓', 'label' => ucfirst($order->shipping_type), 'badge' => 'secondary'];
                        @endphp
                        <tr>
                            <td>{{ $order->order_code }}</td>
                            <td>{{ $order->user->name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $ship['badge'] }}">
                                    {{ $ship['icon'] }} {{ $ship['label'] }}
                                </span>
                            </td>
                            <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge bg-{{ $order->status_color }}">
                                    {{ $statusEmojis[$order->status] ?? '' }}
                                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                </span>
                            </td>
                            <td>{{ $order->midtrans_payment_type ?? '-' }}</td>
                            <td>{{ $order->created_at->format('d M Y H:i') }}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button type="button"
                                            class="btn btn-sm btn-info"
                                            data-bs-toggle="modal"
                                            data-bs-target="#viewModal{{ $order->id }}">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button"
                                            class="btn btn-sm btn-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#updateModal{{ $order->id }}">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No orders found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $orders->links() }}

            </div>
        </div>
    </div>
</div>

{{-- ================= MODALS ================= --}}
@foreach($orders as $order)
@php
    $ship = $shippingLabels[$order->shipping_type] ?? ['icon' => '❓', 'label' => ucfirst($order->shipping_type ?? '-'), 'badge' => 'secondary'];
    $shippingCost = \App\Models\Order::getShippingCost($order->shipping_type ?? 'standard');
@endphp

{{-- VIEW MODAL --}}
<div class="modal fade" id="viewModal{{ $order->id }}">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Order Detail - {{ $order->order_code }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Customer:</strong> {{ $order->user->name }}</p>
                        <p class="mb-1"><strong>Email:</strong> {{ $order->user->email }}</p>
                        <p class="mb-1"><strong>Status:</strong>
                            <span class="badge bg-{{ $order->status_color }}">
                                {{ $statusEmojis[$order->status] ?? '' }}
                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                            </span>
                        </p>
                        <p class="mb-1"><strong>Payment:</strong> {{ $order->midtrans_payment_type ?? '-' }}</p>
                        <p class="mb-0"><strong>Resi:</strong> {{ $order->resi_code ?? '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded border
                            {{ $order->shipping_type === 'pickup' ? 'bg-warning bg-opacity-10 border-warning' : 'bg-light' }}">
                            <p class="mb-1">
                                <strong>Pengiriman:</strong>
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
                                <p class="mb-0"><strong>Alamat:</strong> {{ $order->shipping_address }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <hr>

                @php $subtotal = 0; @endphp
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($order->items ?? [] as $item)
                        @php
                            $itemTotal = $item->price * $item->quantity;
                            $subtotal += $itemTotal;
                        @endphp
                        <tr>
                            <td>{{ $item->product->name ?? 'Deleted Product' }}</td>
                            <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>Rp {{ number_format($itemTotal, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No items</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="text-end">Subtotal Produk</td>
                            <td>Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end">
                                Biaya Pengiriman ({{ $ship['icon'] }} {{ $ship['label'] }})
                            </td>
                            <td>
                                @if($shippingCost === 0)
                                    <span class="text-success">Gratis</span>
                                @else
                                    Rp {{ number_format($shippingCost, 0, ',', '.') }}
                                @endif
                            </td>
                        </tr>
                        <tr class="fw-bold">
                            <td colspan="3" class="text-end">Total</td>
                            <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>

            </div>
        </div>
    </div>
</div>

{{-- UPDATE MODAL --}}
<div class="modal fade" id="updateModal{{ $order->id }}">
    <div class="modal-dialog">
        <div class="modal-content">

            <form action="{{ route('admin.orders.update-status', $order) }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="modal-header">
                    <h5 class="modal-title">Update Status - {{ $order->order_code }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <p class="text-muted small mb-2">
                        {{ $ship['icon'] }} Pengiriman: <strong>{{ $ship['label'] }}</strong>
                    </p>

                    <select name="status" class="form-select status-select" required>
                        @foreach (['paid', 'processing', 'shipped', 'delivered'] as $status)
                            <option value="{{ $status }}"
                                {{ $order->status == $status ? 'selected' : '' }}>
                                {{ $statusEmojis[$status] }} {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </option>
                        @endforeach
                    </select>

                    {{-- Resi hanya untuk non-pickup --}}
                    @if($order->shipping_type !== 'pickup')
                    <div class="mt-3 resi-field"
                         style="display: {{ $order->status == 'shipped' ? 'block' : 'none' }};">
                        <label class="form-label small text-muted">Nomor Resi</label>
                        <input type="text"
                               name="resi_code"
                               class="form-control"
                               placeholder="Input Resi"
                               value="{{ $order->resi_code }}">
                    </div>
                    @endif

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>

            </form>

        </div>
    </div>
</div>

@endforeach
@endsection

@push('scripts')
<script>
document.querySelectorAll('.status-select').forEach(select => {
    select.addEventListener('change', function () {
        const modal = this.closest('.modal');
        const resiField = modal.querySelector('.resi-field');
        if (!resiField) return;
        resiField.style.display = this.value === 'shipped' ? 'block' : 'none';
    });
});
</script>
@endpush