@extends('layouts.layouts-landing')
@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Order</h1>
                <a href="/" class="text-emerald-600 hover:text-emerald-700 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Continue Shopping
                </a>
            </div>

            <!-- Search & Filter -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <form action="{{ route('user.orders') }}" method="GET" class="flex flex-wrap gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari nomor pesanan..."
                            class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                    </div>

                    <div class="w-full sm:w-auto">
                        <select name="status"
                            class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu
                                Pembayaran</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Sudah Dibayar
                            </option>
                            <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Diproses
                            </option>
                            <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Dikirim</option>
                            <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Diterima
                            </option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan
                            </option>
                        </select>
                    </div>

                    <div class="w-full sm:w-auto">
                        <button type="submit"
                            class="w-full px-6 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 transition-colors">
                            Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Orders List -->
            <div class="space-y-4">
                @php
                    $statusBadgeClasses = [
                        'pending' => 'bg-amber-100 text-amber-800',
                        'paid' => 'bg-blue-100 text-blue-800',
                        'processing' => 'bg-indigo-100 text-indigo-800',
                        'shipped' => 'bg-purple-100 text-purple-800',
                        'delivered' => 'bg-green-100 text-green-800',
                        'cancelled' => 'bg-red-100 text-red-800',
                    ];
                    $statusText = [
                        'pending' => 'Menunggu Pembayaran',
                        'paid' => 'Sudah Dibayar',
                        'processing' => 'Diproses',
                        'shipped' => 'Dikirim',
                        'delivered' => 'Diterima',
                        'cancelled' => 'Dibatalkan',
                    ];
                @endphp

                @forelse ($orders as $order)
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <div class="flex flex-wrap justify-between items-start gap-4">
                            <div>
                                <div class="text-lg font-medium">Pesanan #{{ $order->order_code }}</div>
                                <div class="text-sm text-gray-500">{{ $order->created_at->format('d M Y H:i') }}</div>
                            </div>
                            <span
                                class="px-3 py-1 text-sm font-semibold rounded-full {{ $statusBadgeClasses[$order->status] }}">
                                {{ $statusText[$order->status] }}
                            </span>
                        </div>

                        <div class="mt-4">
                            <div class="text-sm text-gray-600">Total Pesanan:</div>
                            <div class="text-lg font-semibold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                            </div>
                        </div>

                        <div class="mt-4 flex justify-end">
                            <button type="button" onclick="openModal('orderModal{{ $order->id }}')"
                                class="px-4 py-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors">
                                Lihat Detail
                            </button>
                        </div>
                    </div>

                    <!-- Modal Detail dengan Tailwind -->
                    <div id="orderModal{{ $order->id }}"
                        class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                        <div class="relative top-20 mx-auto p-5 w-full max-w-4xl">
                            <div class="relative bg-white rounded-xl shadow-lg">
                                <!-- Modal Header -->
                                <div class="flex items-center justify-between p-4 border-b">
                                    <h3 class="text-xl font-semibold text-gray-900">
                                        Detail Pesanan #{{ $order->order_code }}
                                    </h3>
                                    <button type="button" onclick="closeModal('orderModal{{ $order->id }}')"
                                        class="text-gray-400 hover:text-gray-500">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Modal Body -->
                                <div class="p-6">
                                    <!-- Status -->
                                    <div class="mb-6">
                                        <span
                                            class="px-3 py-1 text-sm font-semibold rounded-full {{ $statusBadgeClasses[$order->status] }}">
                                            {{ $statusText[$order->status] }}
                                        </span>
                                        @if ($order->status === 'shipped' && $order->resi_code)
                                            <div class="mt-2 text-sm text-gray-600">
                                                Nomor Resi: {{ $order->resi_code }}
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Order Items -->
                                    <div class="space-y-4">
                                        <h4 class="font-medium text-lg">Produk yang Dipesan</h4>
                                        <div class="space-y-3">
                                            @foreach ($order->items as $item)
                                                <div class="flex justify-between items-center py-3 border-b">
                                                    <div>
                                                        <div class="font-medium">{{ $item->product->name }}</div>
                                                        <div class="text-sm text-gray-500">
                                                            {{ $item->quantity }} x Rp
                                                            {{ number_format($item->price, 0, ',', '.') }}
                                                        </div>
                                                    </div>
                                                    <div class="font-medium">
                                                        Rp {{ number_format($item->getSubtotal(), 0, ',', '.') }}
                                                    </div>
                                                </div>
                                            @endforeach
                                            <div class="flex justify-between items-center pt-3 font-medium">
                                                <div>Total:</div>
                                                <div>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Shipping Address -->
                                    <div class="mt-6">
                                        <h4 class="font-medium text-lg">Alamat Pengiriman</h4>
                                        <p class="mt-2 text-gray-600">{{ $order->shipping_address }}</p>
                                    </div>
                                </div>

                                <!-- Modal Footer -->
                                <div class="flex justify-end gap-3 p-4 border-t">
                                    <button type="button" onclick="closeModal('orderModal{{ $order->id }}')"
                                        class="px-4 py-2 bg-gray-100 text-gray-800 rounded-lg hover:bg-gray-200 transition-colors">
                                        Tutup
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-xl shadow-sm p-6 text-center text-gray-500">
                        Belum ada pesanan
                    </div>
                @endforelse

                <!-- Pagination -->
                @if ($orders->hasPages())
                    <div class="mt-6">
                        {{ $orders->links() }}
                    </div>
                @endif
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

        // Menutup modal ketika mengklik area di luar modal
        window.onclick = function(event) {
            const modals = document.getElementsByClassName('fixed inset-0');
            for (const modal of modals) {
                if (event.target === modal) {
                    modal.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                }
            }
        }
    </script>
@endsection
