<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_code',
        'user_id',
        'total_amount',
        'status',
        'shipping_address',
        'shipping_type',
        'notes',
        'midtrans_transaction_id',
        'midtrans_payment_type',
        'snap_token',
        'resi_code',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    public const STATUSES = [
        'paid',
        'processing',
        'shipped',
        'delivered',
    ];

    public const SHIPPING_TYPES = [
        'standard' => ['label' => 'Standard Delivery', 'cost' => 10000],
        'instant'  => ['label' => 'Instant Delivery',  'cost' => 25000],
        'pickup'   => ['label' => 'Ambil di Tempat',   'cost' => 0],
    ];

    public static function getShippingCost(string $shippingType): int
    {
        return self::SHIPPING_TYPES[$shippingType]['cost'] ?? 10000;
    }

    public static function generateOrderCode(): string
    {
        return DB::transaction(function () {
            $prefix = 'ORDE';
            $timestamp = now()->format('ymdHis');

            $lastOrder = DB::table('orders')
                ->where('order_code', 'like', $prefix . $timestamp . '%')
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            $sequence = $lastOrder
                ? (intval(substr($lastOrder->order_code, -3)) + 1)
                : 1;

            return $prefix . $timestamp . str_pad($sequence, 3, '0', STR_PAD_LEFT);
        });
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_code)) {
                $order->order_code = self::generateOrderCode();
            }
        });
    }

    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'paid'        => 'info',
            'processing'  => 'primary',
            'shipped'     => 'dark',
            'delivered'   => 'success',
            default       => 'secondary',
        };
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function calculateTotal()
    {
        $itemsTotal = $this->items->sum(fn($item) => $item->price * $item->quantity);
        $shippingCost = self::getShippingCost($this->shipping_type ?? 'standard');

        $this->update([
            'total_amount' => number_format($itemsTotal + $shippingCost, 2, '.', '')
        ]);

        return $this;
    }
}