@extends('layouts.layouts-landing')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex items-center justify-between mb-8">
            <h1 class="text-2xl font-bold text-gray-900">📦 Order</h1>

            <a href="/" class="text-purple-600 hover:text-purple-700 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Continue Shopping
            </a>
        </div>

        <!-- Orders List -->
        <div class="space-y-4">

            @php
                $statusBadgeClasses = [
                    'paid'       => 'bg-blue-100 text-blue-800',
                    'processing' => 'bg-indigo-100 text-indigo-800',
                    'shipped'    => 'bg-purple-100 text-purple-800',
                    'delivered'  => 'bg-green-100 text-green-800',
                ];

                $statusText = [
                    'paid'       => '💳 Sudah Dibayar',
                    'processing' => '⚙️ Diproses',
                    'shipped'    => '🚚 Dikirim',
                    'delivered'  => '📦 Diterima',
                ];
            @endphp

            @forelse ($orders as $order)

            <div class="bg-white rounded-xl shadow-sm p-6">

                <div class="flex flex-wrap justify-between items-start gap-4">
                    <div>
                        <div class="text-lg font-medium">
                            Pesanan #{{ $order->order_code }}
                        </div>
                        <div class="text-sm text-gray-500">
                            {{ $order->created_at->format('d M Y H:i') }}
                        </div>
                    </div>

                    <span class="px-3 py-1 text-sm font-semibold rounded-full
                        {{ $statusBadgeClasses[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                        {{ $statusText[$order->status] ?? $order->status }}
                    </span>
                </div>

                <div class="mt-4">
                    <div class="text-sm text-gray-600">Total Pesanan:</div>
                    <div class="text-lg font-semibold">
                        Rp {{ number_format($order->total_amount,0,',','.') }}
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <button type="button"
                        onclick="openModal('orderModal{{ $order->id }}')"
                        class="px-4 py-2 text-purple-600 hover:bg-purple-50 rounded-lg">
                        👁️ Lihat Detail
                    </button>
                </div>

            </div>

            <!-- MODAL DETAIL ORDER -->
            <div id="orderModal{{ $order->id }}"
                class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">

                <div class="relative top-20 mx-auto p-5 w-full max-w-4xl">
                    <div class="relative bg-white rounded-xl shadow-lg">

                        <div class="flex items-center justify-between p-4 border-b">
                            <h3 class="text-xl font-semibold text-gray-900">
                                📦 Detail Pesanan #{{ $order->order_code }}
                            </h3>
                            <button onclick="closeModal('orderModal{{ $order->id }}')"
                                class="text-gray-400 hover:text-gray-500">
                                ✕
                            </button>
                        </div>

                        <div class="p-6">

                            <div class="mb-6">
                                <span class="px-3 py-1 text-sm font-semibold rounded-full
                                    {{ $statusBadgeClasses[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $statusText[$order->status] ?? $order->status }}
                                </span>
                            </div>

                            <!-- PRODUK -->
                            <div class="space-y-4">

                                <h4 class="font-medium text-lg">🛍️ Produk yang Dipesan</h4>

                                @php $subtotal = 0; @endphp

                                @foreach ($order->items as $item)
                                @php
                                    $itemTotal = $item->price * $item->quantity;
                                    $subtotal += $itemTotal;
                                @endphp

                                <div class="flex justify-between items-center py-3 border-b">
                                    <div>
                                        <div class="font-medium">{{ $item->product->name }}</div>
                                        <div class="text-sm text-gray-500">
                                            {{ $item->quantity }} x Rp {{ number_format($item->price,0,',','.') }}
                                        </div>
                                    </div>
                                    <div class="font-medium">
                                        Rp {{ number_format($itemTotal,0,',','.') }}
                                    </div>
                                </div>
                                @endforeach

                                <!-- SUBTOTAL -->
                                <div class="flex justify-between pt-3">
                                    <div>Subtotal Produk</div>
                                    <div class="font-medium">
                                        Rp {{ number_format($subtotal,0,',','.') }}
                                    </div>
                                </div>

                                <!-- JENIS PENGIRIMAN + ESTIMASI -->
                                <div class="flex justify-between pt-2">
                                    <div>🚚 Jenis Pengiriman</div>
                                    <div class="font-medium text-right">
                                        @if($order->shipping_type == 'instant')
                                            ⚡ Instant Delivery
                                            <div class="text-xs text-gray-500">⏱ Estimasi: 2-3 jam</div>
                                        @elseif($order->shipping_type == 'pickup')
                                            🏪 Ambil di Tempat
                                            <div class="text-xs text-gray-500">⏱ Siap diambil sesuai jam operasional toko</div>
                                        @else
                                            🚚 Standard Delivery
                                            <div class="text-xs text-gray-500">⏱ Estimasi: 3-5 hari</div>
                                        @endif
                                    </div>
                                </div>

                                <!-- BIAYA LAYANAN -->
                                <div class="flex justify-between pt-2">
                                    <div>💰 Biaya Layanan</div>
                                    <div class="font-medium">
                                        @if($order->shipping_type == 'instant')
                                            Rp 25.000
                                        @elseif($order->shipping_type == 'pickup')
                                            <span class="text-green-600">Gratis</span>
                                        @else
                                            Rp 10.000
                                        @endif
                                    </div>
                                </div>

                                <!-- TOTAL -->
                                <div class="flex justify-between items-center pt-4 border-t text-lg font-semibold">
                                    <div>Total Pembayaran</div>
                                    <div class="text-purple-600">
                                        Rp {{ number_format($order->total_amount,0,',','.') }}
                                    </div>
                                </div>

                            </div>

                            <!-- ALAMAT / INFO PICKUP -->
                            <div class="mt-6">
                                @if($order->shipping_type == 'pickup')
                                    <h4 class="font-medium text-lg">🏪 Info Pengambilan</h4>
                                    <div class="mt-2 p-4 bg-amber-50 border border-amber-200 rounded-lg text-sm text-gray-700 space-y-1">
                                        <p>📍 <span class="font-medium">Alamat Toko:</span> {{ config('app.store_address', 'Samping Gerbang BTP') }}</p>
                                        <p>🕐 <span class="font-medium">Jam Operasional:</span> {{ config('app.store_hours', 'Senin – Minggu, 09.00 – 21.00') }}</p>
                                        <p class="text-amber-700">⚠️ Harap tunjukkan kode pesanan <strong>#{{ $order->order_code }}</strong> saat pengambilan.</p>
                                    </div>
                                @else
                                    <h4 class="font-medium text-lg">📍 Alamat Pengiriman</h4>
                                    <p class="mt-2 text-gray-600">{{ $order->shipping_address }}</p>
                                @endif
                            </div>

                        </div>

                        <div class="flex justify-end p-4 border-t">
                            <button onclick="closeModal('orderModal{{ $order->id }}')"
                                class="px-4 py-2 bg-gray-100 rounded-lg hover:bg-gray-200">
                                Tutup
                            </button>
                        </div>

                    </div>
                </div>
            </div>

            @empty
            <div class="bg-white rounded-xl shadow-sm p-6 text-center text-gray-500">
                📭 Belum ada pesanan
            </div>
            @endforelse

        </div>
    </div>
</div>

<script>
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
    document.body.style.overflow = 'auto';
}
</script>

@endsection
