<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user:id,first_name,last_name,name,email']);

        if ($search = $request->search) {
            $query->where(fn ($q) => $q
                ->where('order_number', 'like', "%{$search}%")
                ->orWhereHas('user', fn ($u) => $u
                    ->where('first_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")));
        }

        if ($status = $request->status) {
            $query->where('status', $status);
        }

        if ($payment = $request->payment_status) {
            $query->where('payment_status', $payment);
        }

        return response()->json(
            $query->latest()->paginate($request->integer('per_page', 15))
        );
    }

    public function show(Order $order)
    {
        return response()->json(
            $order->load([
                'user:id,first_name,last_name,name,email',
                'items.product:id,name',
                'items.product.images',
            ])
        );
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,confirmed,processing,packed,shipped,delivered,cancelled,returned,refunded',
            'note'   => 'nullable|string',
        ]);

        $order->update(['status' => $data['status']]);

        return response()->json($order);
    }

    public function cancel(Request $request, Order $order)
    {
        if (! $order->canBeCancelled()) {
            return response()->json(['message' => 'Order cannot be cancelled at this stage.'], 422);
        }

        $order->update(['status' => 'cancelled']);

        return response()->json($order);
    }

    public function refund(Request $request, Order $order)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string',
        ]);

        $order->update(['status' => 'refunded', 'payment_status' => 'refunded']);

        return response()->json($order);
    }
}
