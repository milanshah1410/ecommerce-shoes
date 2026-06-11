<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'carts';

    protected $fillable = ['user_id', 'product_id', 'variant_id', 'quantity'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function unitPrice(): float
    {
        $base = $this->product?->effective_price ?? 0;
        $adjustment = $this->variant?->price_adjustment ?? 0;

        return (float) $base + (float) $adjustment;
    }

    public function lineTotal(): float
    {
        return round($this->unitPrice() * $this->quantity, 2);
    }
}
