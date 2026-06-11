<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'type', 'discount_value', 'min_order_amount', 'max_discount',
        'start_date', 'end_date', 'usage_limit', 'used_count', 'status',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function isValidFor(float $orderAmount): bool
    {
        if ($this->status !== 'active') {
            return false;
        }
        if ($this->start_date && $this->start_date->isFuture()) {
            return false;
        }
        if ($this->end_date && $this->end_date->isPast()) {
            return false;
        }
        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return false;
        }
        if ($orderAmount < (float) $this->min_order_amount) {
            return false;
        }

        return true;
    }

    public function discountFor(float $orderAmount): float
    {
        $discount = $this->type === 'percentage'
            ? $orderAmount * ((float) $this->discount_value / 100)
            : (float) $this->discount_value;

        if ($this->max_discount !== null) {
            $discount = min($discount, (float) $this->max_discount);
        }

        return round(min($discount, $orderAmount), 2);
    }
}
