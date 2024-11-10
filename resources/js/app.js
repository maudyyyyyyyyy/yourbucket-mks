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
});