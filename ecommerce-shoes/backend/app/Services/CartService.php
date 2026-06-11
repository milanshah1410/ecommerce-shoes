<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Support\Collection;

class CartService
{
    public const TAX_RATE = 0.18;          // 18% GST
    public const FREE_SHIPPING_THRESHOLD = 999;
    public const SHIPPING_CHARGE = 79;

    /**
     * Load the user's cart items with the relations needed for pricing.
     */
    public function items(User $user): Collection
    {
        return $user->cartItems()
            ->with(['product.images', 'product.variants', 'variant'])
            ->get();
    }

    public function subtotal(Collection $items): float
    {
        return round($items->sum(fn ($item) => $item->lineTotal()), 2);
    }

    /**
     * Build a full pricing summary, optionally applying a coupon code.
     *
     * @return array{subtotal: float, tax: float, shipping: float, discount: float, total: float, coupon: ?Coupon}
     */
    public function summary(Collection $items, ?string $couponCode = null): array
    {
        $subtotal = $this->subtotal($items);
        $coupon = null;
        $discount = 0.0;

        if ($couponCode) {
            $coupon = Coupon::where('code', $couponCode)->first();
            if ($coupon && $coupon->isValidFor($subtotal)) {
                $discount = $coupon->discountFor($subtotal);
            } else {
                $coupon = null;
            }
        }

        $taxable = max($subtotal - $discount, 0);
        $tax = round($taxable * self::TAX_RATE, 2);
        $shipping = $subtotal >= self::FREE_SHIPPING_THRESHOLD || $subtotal == 0
            ? 0.0
            : (float) self::SHIPPING_CHARGE;
        $total = round($taxable + $tax + $shipping, 2);

        return compact('subtotal', 'tax', 'shipping', 'discount', 'total', 'coupon');
    }
}
