<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;

class UserCartController extends Controller
{
    public function index()
    {
        $order = Order::with(['items.product.category'])
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->latest()
            ->first();

        $cartItems = $order ? $order->items : collect();

        return view('landing.shopping-cart', compact('cartItems', 'order'));
    }
}