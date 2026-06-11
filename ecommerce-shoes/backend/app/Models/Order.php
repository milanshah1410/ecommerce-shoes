<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'order_number', 'status', 'payment_method', 'payment_status',
        'payment_reference', 'subtotal', 'tax', 'shipping_charge', 'discount',
        'total_amount', 'coupon_code', 'ship_full_name', 'ship_mobile',
        'ship_address_line', 'ship_city', 'ship_state', 'ship_country',
        'ship_pincode', 'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping_charge' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public const CANCELLABLE = ['pending', 'confirmed', 'processing'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, self::CANCELLABLE, true);
    }
}
