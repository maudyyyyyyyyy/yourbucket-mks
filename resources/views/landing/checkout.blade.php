@extends('layouts.layouts-landing')

@section('title', 'Checkout')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Checkout</h1>
            </div>

            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Shipping Form -->
                <div class="flex-1">
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Shipping Information</h2>
                        <form id="checkoutForm" class="space-y-4">
                            @csrf
                            <!-- Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Full Name</label>
                                <input type="text" name="name" value="{{ auth()->user()->name }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                    required>
                            </div>

                            <!-- Phone -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                                <input type="tel" name="phone" value="{{ auth()->user()->phone }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                    required>
                            </div>

                            <!-- Address -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Shipping Address</label>
                                <textarea name="shipping_address" rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                    required>{{ auth()->user()->address }}</textarea>
                            </div>

                            <!-- Notes -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Order Notes (Optional)</label>
                                <textarea name="notes" rows="2"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                    placeholder="Any special instructions for delivery"></textarea>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="w-full lg:w-96">
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Order Summary</h2>

                        <!-- Cart Items -->
                        <div id="orderItems" class="space-y-4 mb-4">
                            <!-- Items will be populated by JavaScript -->
                        </div>

                        <!-- Summary -->
                        <div class="border-t border-gray-200 pt-4 space-y-4">
                            <div class="flex items-center justify-between">
                                <dt class="text-sm text-gray-600">Subtotal</dt>
                                <dd class="text-sm font-medium text-gray-900" data-summary="subtotal"></dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-sm text-gray-600">Shipping</dt>
                                <dd class="text-sm font-medium text-gray-900" data-summary="shipping"></dd>
                            </div>
                            <div class="border-t border-gray-200 pt-4 flex items-center justify-between">
                                <dt class="text-base font-medium text-gray-900">Total</dt>
                                <dd class="text-base font-medium text-gray-900" data-summary="total"></dd>
                            </div>
                        </div>

                        <!-- Place Order Button -->
                        <button type="button" onclick="placeOrder()"
                            class="mt-6 w-full bg-emerald-600 text-white py-3 px-4 rounded-lg hover:bg-emerald-700">
                            Place Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('services.midtrans.client_key') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const cart = window.cart;
            if (!cart || !cart.items || cart.items.length === 0) {
                window.location.href = '/cart';
                return;
            }

            // Populate order items
            const orderItems = document.getElementById('orderItems');
            orderItems.innerHTML = '';

            cart.items.forEach(item => {
                const div = document.createElement('div');
                div.className = 'flex justify-between items-start';
                div.innerHTML = `
            <div class="flex-1">
                <h4 class="text-sm font-medium text-gray-900">${item.name}</h4>
                <p class="mt-1 text-xs text-gray-500">${item.quantity} x ${cart.formatPrice(item.price)}</p>
            </div>
            <span class="text-sm font-medium text-gray-900">
                ${cart.formatPrice(item.price * item.quantity)}
            </span>
        `;
                orderItems.appendChild(div);
            });

            // Update summary
            const subtotal = cart.calculateSubtotal();
            const shipping = 20000;
            const total = subtotal + shipping;

            document.querySelector('[data-summary="subtotal"]').textContent = cart.formatPrice(subtotal);
            document.querySelector('[data-summary="shipping"]').textContent = cart.formatPrice(shipping);
            document.querySelector('[data-summary="total"]').textContent = cart.formatPrice(total);
        });

        function placeOrder() {
            const form = document.getElementById('checkoutForm');

            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const formData = {
                name: form.querySelector('[name="name"]').value,
                phone: form.querySelector('[name="phone"]').value,
                shipping_address: form.querySelector('[name="shipping_address"]').value,
                notes: form.querySelector('[name="notes"]').value,
                cart: window.cart.items
            };

            // Process checkout
            fetch('/checkout/process', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }

                    // Tampilkan modal pembayaran Midtrans
                    window.snap.pay(data.snap_token, {
                        onSuccess: function(result) {
                            // Hapus cart dari localStorage
                            localStorage.removeItem('shopping_cart');
                            window.location.href = `/order/${data.order_id}`;
                        },
                        onPending: function(result) {
                            window.location.href = `/order/${data.order_id}`;
                        },
                        onError: function(result) {
                            alert('Payment failed. Please try again.');
                        },
                        onClose: function() {
                            const continuePayment = confirm('Do you want to continue with the payment?');
                            if (continuePayment) {
                                window.location.href = `/order/${data.order_id}`;
                            }
                        }
                    });
                })
                .catch(error => {
                    alert('Error processing order: ' + error.message);
                });
        }
    </script>
@endpush
