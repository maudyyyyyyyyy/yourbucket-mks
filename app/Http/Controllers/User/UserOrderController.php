<?php

namespace App\Http\Controllers\User;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserOrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with(['items.product'])
            ->where('user_id', auth()->id())
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->search, function ($query, $search) {
                return $query->where('order_code', 'like', '%' . $search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('landing.order', compact('orders'));
    }
}
