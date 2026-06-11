<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService) {}

    public function index(Request $request)
    {
        $items = $this->cartService->items($request->user());
        $summary = $this->cartService->summary($items, $request->get('coupon'));

        return $this->respond($items, $summary);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'variant_id' => ['nullable', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        if (! empty($data['variant_id'])) {
            $variant = ProductVariant::findOrFail($data['variant_id']);
            if ($variant->product_id !== (int) $data['product_id']) {
                throw ValidationException::withMessages(['variant_id' => ['Variant does not belong to product.']]);
            }
            if ($variant->stock < $data['quantity']) {
                throw ValidationException::withMessages(['quantity' => ['Not enough stock available.']]);
            }
        }

        $item = Cart::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'product_id' => $data['product_id'],
                'variant_id' => $data['variant_id'] ?? null,
            ],
            ['quantity' => $data['quantity']],
        );

        $items = $this->cartService->items($request->user());

        return $this->respond($items, $this->cartService->summary($items), 201);
    }

    public function update(Request $request, Cart $cart)
    {
        $this->authorizeOwner($request, $cart);

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        if ($cart->variant && $cart->variant->stock < $data['quantity']) {
            throw ValidationException::withMessages(['quantity' => ['Not enough stock available.']]);
        }

        $cart->update($data);

        $items = $this->cartService->items($request->user());

        return $this->respond($items, $this->cartService->summary($items));
    }

    public function destroy(Request $request, Cart $cart)
    {
        $this->authorizeOwner($request, $cart);
        $cart->delete();

        $items = $this->cartService->items($request->user());

        return $this->respond($items, $this->cartService->summary($items));
    }

    public function applyCoupon(Request $request)
    {
        $request->validate(['code' => ['required', 'string']]);

        $items = $this->cartService->items($request->user());
        $subtotal = $this->cartService->subtotal($items);

        $coupon = Coupon::where('code', $request->code)->first();
        if (! $coupon || ! $coupon->isValidFor($subtotal)) {
            throw ValidationException::withMessages(['code' => ['This coupon is invalid or not applicable.']]);
        }

        return $this->respond($items, $this->cartService->summary($items, $request->code));
    }

    private function respond($items, array $summary, int $status = 200)
    {
        return response()->json([
            'data' => CartResource::collection($items),
            'count' => $items->sum('quantity'),
            'summary' => [
                'subtotal' => $summary['subtotal'],
                'tax' => $summary['tax'],
                'shipping' => $summary['shipping'],
                'discount' => $summary['discount'],
                'total' => $summary['total'],
                'coupon' => $summary['coupon']?->code,
            ],
        ], $status);
    }

    private function authorizeOwner(Request $request, Cart $cart): void
    {
        abort_unless($cart->user_id === $request->user()->id, 403);
    }
}
