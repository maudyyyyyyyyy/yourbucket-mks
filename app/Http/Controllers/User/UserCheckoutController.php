<?php

namespace App\Http\Controllers\User;

use Exception;
use Midtrans\Snap;
use Midtrans\Config;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class UserCheckoutController extends Controller
{
    public function __construct()
    {
        try {
            $serverKey = config('midtrans.server_key');

            if (empty($serverKey)) {
                throw new Exception('Midtrans server key is not configured. Please check your .env file');
            }

            Config::$serverKey = $serverKey;
            Config::$isProduction = config('midtrans.is_production');
            Config::$isSanitized = config('midtrans.sanitize');
            Config::$is3ds = config('midtrans.enable_3ds');

            Log::info('Midtrans Configuration Loaded', [
                'isProduction' => Config::$isProduction,
                'isSanitized' => Config::$isSanitized,
                'is3ds' => Config::$is3ds
            ]);
        } catch (Exception $e) {
            Log::error('Midtrans configuration error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function process(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => ['required', 'string'],
                'phone' => ['required', 'string'],
                'shipping_address' => ['required', 'string'],
                'notes' => ['nullable', 'string'],
                'cart' => ['required', 'array']
            ]);

            DB::beginTransaction();

            $order = Order::create([
                'user_id' => auth()->id(),
                'shipping_address' => $request->shipping_address,
                'total_amount' => 0,
                'status' => 'pending',
                'notes' => $request->notes
            ]);

            $totalAmount = 0;
            $items = [];

            foreach ($request->cart as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ]);

                $totalAmount += ($item['price'] * $item['quantity']);

                $items[] = [
                    'id' => (string) $item['id'],
                    'price' => (int) $item['price'],
                    'quantity' => (int) $item['quantity'],
                    'name' => $item['name']
                ];
            }

            $shippingCost = 20000;
            $totalAmount += $shippingCost;

            $order->update(['total_amount' => $totalAmount]);

            $params = [
                'transaction_details' => [
                    'order_id' => (string) $order->id,
                    'gross_amount' => (int) $totalAmount,
                ],
                'item_details' => array_merge($items, [
                    [
                        'id' => 'shipping',
                        'price' => $shippingCost,
                        'quantity' => 1,
                        'name' => 'Shipping Cost'
                    ]
                ]),
                'customer_details' => [
                    'first_name' => $request->name,
                    'email' => auth()->user()->email,
                    'phone' => $request->phone,
                    'billing_address' => [
                        'address' => $request->shipping_address
                    ],
                    'shipping_address' => [
                        'address' => $request->shipping_address
                    ]
                ]
            ];

            $snapToken = Snap::getSnapToken($params);

            if (empty($snapToken)) {
                throw new Exception('Failed to generate Snap Token');
            }

            $order->update(['snap_token' => $snapToken]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'snap_token' => $snapToken,
                'order_id' => $order->id
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Checkout process error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error processing payment: ' . $e->getMessage()
            ], 422);
        }
    }

    public function updateStatus(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required',
                'transaction_id' => 'required',
                'payment_type' => 'required',
                'status' => 'required|in:paid,pending,cancelled'
            ]);

            $order = Order::findOrFail($request->order_id);

            // Pastikan user hanya bisa update ordernya sendiri
            if ($order->user_id !== auth()->id()) {
                throw new Exception('Unauthorized access');
            }

            $order->update([
                'status' => $request->status,
                'midtrans_transaction_id' => $request->transaction_id,
                'midtrans_payment_type' => $request->payment_type
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Order status updated successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Error updating order status: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }
}