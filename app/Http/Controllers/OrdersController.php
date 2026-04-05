<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Exception;

class OrdersController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with(['user', 'items.product'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('order_code', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->shipping_type, function ($query, $shippingType) {
                $query->where('shipping_type', $shippingType);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.order', compact('orders'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $allowedStatuses = ['paid', 'processing', 'shipped', 'delivered'];

        $request->validate([
            'status'    => 'required|in:' . implode(',', $allowedStatuses),
            'resi_code' => 'nullable|string'
        ]);

        // Resi wajib untuk shipped, kecuali pickup
        if (
            $request->status === 'shipped' &&
            $order->shipping_type !== 'pickup' &&
            empty($request->resi_code)
        ) {
            return back()->with('error', 'Resi wajib diisi jika status Shipped.');
        }

        $order->update([
            'status'    => $request->status,
            'resi_code' => $request->status === 'shipped'
                ? $request->resi_code
                : $order->resi_code,
        ]);

        return back()->with('success', 'Status berhasil diperbarui.');
    }
}