<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class ProductDetailsController extends Controller
{
    public function index($slug)
    {
        $query = Product::where('slug', $slug)->firstOrFail();

        $categories = Category::where('id', $query->category_id)->first();

        $products = Product::where('category_id', $categories->id)->paginate(4);

        return view('landing.detail', compact('query','products'));
    }

    public function addToCart($id)
    {
        $product = Product::findOrFail($id);

        $order = Order::firstOrCreate([
            'user_id' => auth()->id(),
            'status' => 'pending'
        ],[
            'total_amount' => 0
        ]);

        $item = OrderItem::where('order_id', $order->id)
            ->where('product_id', $product->id)
            ->first();

        if ($item) {
            $item->quantity += 1;
            $item->save();
        } else {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => 1,
                'price' => $product->price
            ]);
        }

        return redirect()->route('cart')->with('success','Produk ditambahkan ke cart');
    }
}