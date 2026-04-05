@extends('layouts.layouts-landing')

@section('title', 'Shopping Cart')

@section('content')
    @auth
        <div id="userData" data-name="{{ auth()->user()->name }}" data-phone="{{ auth()->user()->phone }}"
            data-address="{{ auth()->user()->address }}" class="hidden">
        </div>
    @endauth

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('warning'))
                <div class="mb-4 p-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700">
                    Please login first to proceed with checkout
                </div>
            @endif

            <div class="flex items-center justify-between mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Shopping Cart</h1>
                <a href="/" class="text-purple-600 hover:text-purple-700 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Continue Shopping
                </a>
            </div>

            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Cart Items Container -->
                <div class="flex-1">
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden cart-items"
                        data-logged-in="{{ auth()->check() ? 'true' : 'false' }}">
                        <!-- Cart items will be populated by JavaScript -->
                    </div>

                    {{-- ✅ Warning stok tidak cukup --}}
                    <div id="stockWarning" class="hidden mt-4 p-4 bg-red-50 border border-red-300 rounded-xl text-sm text-red-700">
                        <div class="font-semibold mb-1">⚠️ Stok Tidak Cukup</div>
                        <ul id="stockWarningList" class="list-disc list-inside space-y-1"></ul>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="w-full lg:w-96">
                    <div class="bg-white rounded-xl shadow-sm p-6">

                        <h2 class="text-lg font-medium text-gray-900">Order Summary</h2>

                        <dl class="mt-6 space-y-4">

                            <!-- SUBTOTAL -->
                            <div class="flex items-center justify-between">
                                <dt class="text-sm text-gray-600">Subtotal</dt>
                                <dd class="text-sm font-medium text-gray-900" data-summary="subtotal">Rp 0</dd>
                            </div>

                            <!-- SHIPPING SELECT -->
                            <div>
                                <label class="text-sm text-gray-600">Pilih Pengiriman</label>
                                <select id="shippingType" class="w-full border rounded-lg p-2 mt-1">
                                    <option value="standard" data-cost="10000" data-estimate="3-5 hari">
                                        🚚 Standard Delivery (Rp 10.000)
                                    </option>
                                    <option value="instant" data-cost="25000" data-estimate="2-3 jam">
                                        ⚡ Instant Delivery (Rp 25.000)
                                    </option>
                                    <option value="pickup" data-cost="0" data-estimate="Siap diambil sesuai jam operasional">
                                        🏪 Ambil di Tempat (Gratis)
                                    </option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">
                                    ⏱ Estimasi: <span id="shippingEstimate">3-5 hari</span>
                                </p>
                            </div>

                            <!-- INFO PICKUP -->
                            <div id="pickupInfo" class="hidden p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-gray-700 space-y-1">
                                <p>📍 <span class="font-medium">Alamat Toko:</span> {{ config('app.store_address', 'Samping Gerbang BTP') }}</p>
                                <p>🕐 <span class="font-medium">Jam Operasional:</span> {{ config('app.store_hours', 'Senin – Minggu, 09.00 – 21.00') }}</p>
                                <p class="text-amber-700">⚠️ Tunjukkan kode pesanan saat pengambilan.</p>
                            </div>

                            <!-- SHIPPING COST -->
                            <div class="flex items-center justify-between">
                                <dt class="text-sm text-gray-600">Shipping</dt>
                                <dd class="text-sm font-medium text-gray-900" id="shippingCost">Rp 10.000</dd>
                            </div>

                            <!-- TOTAL -->
                            <div class="border-t border-gray-200 pt-4 flex items-center justify-between">
                                <dt class="text-base font-medium text-gray-900">Total</dt>
                                <dd class="text-base font-medium text-gray-900" data-summary="total">Rp 0</dd>
                            </div>

                        </dl>

                        @if (auth()->check())
                            <button data-action="checkout"
                                class="mt-6 w-full bg-gray-300 cursor-not-allowed text-white py-3 px-4 rounded-lg">
                                Proceed to Checkout
                            </button>

                            <button id="payButton"
                                class="mt-6 w-full bg-purple-600 hover:bg-purple-700 text-white py-3 px-4 rounded-lg hidden">
                                Pay Now
                            </button>
                        @else
                            <a href="{{ route('login') }}"
                                class="mt-6 w-full bg-purple-600 hover:bg-purple-700 text-white py-3 px-4 rounded-lg text-center block">
                                Login to Checkout
                            </a>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ config('midtrans.payment_url') }}"
    data-client-key="{{ config('midtrans.client_key') }}">
</script>

<script>
    const shippingSelect  = document.getElementById('shippingType');
    const shippingEstimate = document.getElementById('shippingEstimate');
    const shippingCostEl  = document.getElementById('shippingCost');
    const pickupInfo      = document.getElementById('pickupInfo');
    const stockWarning    = document.getElementById('stockWarning');
    const stockWarningList = document.getElementById('stockWarningList');

    function formatRupiah(amount) {
        return 'Rp ' + amount.toLocaleString('id-ID');
    }

    function updateShipping() {
        const selected = shippingSelect.options[shippingSelect.selectedIndex];
        const cost     = parseInt(selected.dataset.cost);
        const estimate = selected.dataset.estimate;
        const value    = selected.value;

        shippingEstimate.textContent = estimate;
        shippingCostEl.textContent   = cost === 0 ? 'Gratis' : formatRupiah(cost);
        pickupInfo.classList.toggle('hidden', value !== 'pickup');

        const subtotalEl = document.querySelector('[data-summary="subtotal"]');
        const totalEl    = document.querySelector('[data-summary="total"]');

        if (subtotalEl && totalEl) {
            const subtotal = parseInt(subtotalEl.textContent.replace(/[^0-9]/g, '')) || 0;
            totalEl.textContent = formatRupiah(subtotal + cost);
        }
    }

    // ✅ Cek stok semua item di cart ke backend
    async function checkStock(cartItems) {
        if (!cartItems || cartItems.length === 0) return;

        const ids = cartItems.map(item => item.id);

        try {
            const res = await fetch(`/api/products/stock?ids[]=${ids.join('&ids[]=')}`)
            const products = await res.json();

            const outOfStock = [];

            cartItems.forEach(cartItem => {
                const product = products.find(p => p.id == cartItem.id);
                if (product && product.stock < cartItem.quantity) {
                    outOfStock.push(
                        `${cartItem.name}: stok tersedia <strong>${product.stock}</strong>, kamu memesan <strong>${cartItem.quantity}</strong>`
                    );
                }
            });

            if (outOfStock.length > 0) {
                // Tampilkan warning
                stockWarningList.innerHTML = outOfStock.map(msg => `<li>${msg}</li>`).join('');
                stockWarning.classList.remove('hidden');

                // ✅ Disable tombol checkout
                const checkoutBtn = document.querySelector('[data-action="checkout"]');
                const payBtn = document.getElementById('payButton');
                if (checkoutBtn) {
                    checkoutBtn.disabled = true;
                    checkoutBtn.classList.add('bg-red-300', 'cursor-not-allowed');
                    checkoutBtn.classList.remove('bg-gray-300', 'bg-purple-600');
                    checkoutBtn.textContent = '⚠️ Stok Tidak Cukup';
                }
                if (payBtn) {
                    payBtn.disabled = true;
                }
            } else {
                // Sembunyikan warning jika stok cukup
                stockWarning.classList.add('hidden');
            }
        } catch (e) {
            console.error('Gagal cek stok:', e);
        }
    }

    shippingSelect.addEventListener('change', updateShipping);
    updateShipping();

    // ✅ Panggil checkStock setelah cart di-render oleh JS lain
    // Tunggu sebentar supaya cart items sudah ter-render
    setTimeout(() => {
        const cart = JSON.parse(localStorage.getItem('cart') || '[]');
        checkStock(cart);
    }, 500);
</script>
@endpush