<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function stats()
    {
        // Recent orders — last 10 with user info
        $recentOrders = Order::with(['user:id,first_name,last_name,name'])
            ->latest()
            ->limit(10)
            ->get(['id', 'order_number', 'status', 'total_amount', 'created_at', 'user_id'])
            ->map(fn ($order) => [
                'id'           => $order->id,
                'order_number' => $order->order_number,
                'status'       => $order->status,
                'total_amount' => (float) $order->total_amount,
                'created_at'   => $order->created_at?->toDateTimeString(),
                'customer'     => $order->user?->full_name,
            ]);

        // Top 5 products by sold_count
        $topProducts = Product::select(['id', 'name', 'sold_count'])
            ->with(['images'])
            ->orderByDesc('sold_count')
            ->limit(5)
            ->get()
            ->map(fn ($p) => [
                'id'         => $p->id,
                'name'       => $p->name,
                'sold_count' => $p->sold_count,
                'thumbnail'  => optional($p->images->where('is_primary', true)->first())->image
                                ?? optional($p->images->first())->image,
            ]);

        // Low stock variants
        $lowStock = ProductVariant::where('stock', '<', 5)
            ->with('product:id,name')
            ->orderBy('stock')
            ->limit(10)
            ->get(['id', 'product_id', 'size', 'color', 'stock']);

        // Order status breakdown
        $orderStatuses = Order::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // Monthly revenue — last 12 months
        $monthlyRevenue = Order::where('payment_status', 'paid')
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn ($row) => [
                'month'   => $row->month,
                'revenue' => (float) $row->revenue,
            ]);

        return response()->json([
            'total_orders'    => Order::count(),
            'total_revenue'   => (float) Order::where('payment_status', 'paid')->sum('total_amount'),
            'total_customers' => User::where('role', 'customer')->count(),
            'total_products'  => Product::count(),
            'recent_orders'   => $recentOrders,
            'top_products'    => $topProducts,
            'low_stock'       => $lowStock,
            'order_statuses'  => $orderStatuses,
            'monthly_revenue' => $monthlyRevenue,
        ]);
    }
}
