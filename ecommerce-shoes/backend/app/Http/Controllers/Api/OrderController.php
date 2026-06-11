<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function __construct(private readonly CartService $cartService) {}

    public function index(Request $request)
    {
        $orders = $request->user()->orders()
            ->withCount('items')
            ->latest()
            ->paginate(10);

        return OrderResource::collection($orders);
    }

    public function show(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        $order->load('items.product.images');

        return new OrderResource($order);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'payment_method' => ['required', 'in:cod,razorpay,stripe'],
            'coupon_code' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:500'],
            'shipping.full_name' => ['required', 'string', 'max:150'],
            'shipping.mobile' => ['required', 'string', 'max:20'],
            'shipping.address_line' => ['required', 'string', 'max:255'],
            'shipping.city' => ['required', 'string', 'max:100'],
            'shipping.state' => ['required', 'string', 'max:100'],
            'shipping.country' => ['required', 'string', 'max:100'],
            'shipping.pincode' => ['required', 'string', 'max:12'],
        ]);

        $user = $request->user();
        $items = $this->cartService->items($user);

        if ($items->isEmpty()) {
            throw ValidationException::withMessages(['cart' => ['Your cart is empty.']]);
        }

        $summary = $this->cartService->summary($items, $data['coupon_code'] ?? null);

        $order = DB::transaction(function () use ($user, $items, $summary, $data) {
            // Re-check stock and decrement atomically.
            foreach ($items as $item) {
                if ($item->variant) {
                    $affected = $item->variant->newQuery()
                        ->whereKey($item->variant_id)
                        ->where('stock', '>=', $item->quantity)
                        ->decrement('stock', $item->quantity);

                    if ($affected === 0) {
                        throw ValidationException::withMessages([
                            'cart' => ["'{$item->product->name}' is out of stock in the selected size/color."],
                        ]);
                    }
                }
            }

            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'ORD-'.strtoupper(Str::random(10)),
                'status' => 'pending',
                'payment_method' => $data['payment_method'],
                'payment_status' => $data['payment_method'] === 'cod' ? 'pending' : 'pending',
                'subtotal' => $summary['subtotal'],
                'tax' => $summary['tax'],
                'shipping_charge' => $summary['shipping'],
                'discount' => $summary['discount'],
                'total_amount' => $summary['total'],
                'coupon_code' => $summary['coupon']?->code,
                'ship_full_name' => $data['shipping']['full_name'],
                'ship_mobile' => $data['shipping']['mobile'],
                'ship_address_line' => $data['shipping']['address_line'],
                'ship_city' => $data['shipping']['city'],
                'ship_state' => $data['shipping']['state'],
                'ship_country' => $data['shipping']['country'],
                'ship_pincode' => $data['shipping']['pincode'],
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'product_name' => $item->product->name,
                    'size' => $item->variant?->size,
                    'color' => $item->variant?->color,
                    'price' => $item->unitPrice(),
                    'quantity' => $item->quantity,
                    'subtotal' => $item->lineTotal(),
                ]);

                $item->product()->increment('sold_count', $item->quantity);
            }

            if ($summary['coupon']) {
                Coupon::whereKey($summary['coupon']->id)->increment('used_count');
            }

            $user->cartItems()->delete();

            return $order;
        });

        $order->load('items.product.images');

        return response()->json([
            'data' => new OrderResource($order),
            'message' => 'Order placed successfully.',
        ], 201);
    }

    public function cancel(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        if (! $order->canBeCancelled()) {
            throw ValidationException::withMessages(['order' => ['This order can no longer be cancelled.']]);
        }

        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                if ($item->variant_id) {
                    \App\Models\ProductVariant::whereKey($item->variant_id)->increment('stock', $item->quantity);
                }
            }
            $order->update(['status' => 'cancelled']);
        });

        return response()->json(['message' => 'Order cancelled.', 'data' => new OrderResource($order)]);
    }
}
