import './bootstrap';
import './search';
import 'flowbite';
import ShoppingCart from './cart';

document.addEventListener('DOMContentLoaded', () => {

    try {
        window.cart = new ShoppingCart();
        console.log('Shopping cart initialized');
    } catch (error) {
        console.error('Error initializing cart:', error);
    }

    const shippingSelect = document.getElementById('shippingType');
    const shippingCostEl = document.getElementById('shippingCost');
    const shippingEstimateEl = document.getElementById('shippingEstimate');
    const pickupInfo = document.getElementById('pickupInfo');
    const subtotalEl = document.querySelector('[data-summary="subtotal"]');
    const totalEl = document.querySelector('[data-summary="total"]');

    function formatRupiah(number) {
        return number === 0 ? 'Gratis' : 'Rp ' + number.toLocaleString('id-ID');
    }

    function updateTotal() {
        if (!shippingSelect) return;

        const selectedOption = shippingSelect.options[shippingSelect.selectedIndex];
        const cost = parseInt(selectedOption.dataset.cost) || 0;
        const estimate = selectedOption.dataset.estimate || '-';
        const subtotal = window.cart ? window.cart.calculateSubtotal() : 0;
        const isPickup = shippingSelect.value === 'pickup';

        if (shippingCostEl) shippingCostEl.innerText = formatRupiah(cost);
        if (shippingEstimateEl) shippingEstimateEl.innerText = estimate;
        if (subtotalEl) subtotalEl.innerText = 'Rp ' + subtotal.toLocaleString('id-ID');
        if (totalEl) totalEl.innerText = 'Rp ' + (subtotal + cost).toLocaleString('id-ID');

        // Tampil/sembunyikan info pickup
        if (pickupInfo) {
            pickupInfo.classList.toggle('hidden', !isPickup);
        }

        // Sync toggle field alamat di shipping form (jika sudah terbuka)
        if (window.cart) {
            window.cart.toggleAddressField(shippingSelect.value);
        }
    }

    if (shippingSelect) {
        shippingSelect.addEventListener('change', updateTotal);
    }

    setTimeout(updateTotal, 300);

});
