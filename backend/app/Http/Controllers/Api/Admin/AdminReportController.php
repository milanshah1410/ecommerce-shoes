<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminReportController extends Controller
{
    public function sales(Request $request)
    {
        $request->validate([
            'from'     => 'required|date',
            'to'       => 'required|date|after_or_equal:from',
            'group_by' => 'in:day,week,month',
        ]);

        $groupBy = $request->input('group_by', 'day');

        $format = match ($groupBy) {
            'month' => '%Y-%m',
            'week'  => '%Y-%u',
            default => '%Y-%m-%d',
        };

        $rows = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$request->from.' 00:00:00', $request->to.' 23:59:59'])
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$format}') as period"),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('AVG(total_amount) as avg_order_value'),
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->map(fn ($r) => [
                'period'          => $r->period,
                'orders'          => (int) $r->orders,
                'revenue'         => (float) $r->revenue,
                'avg_order_value' => round((float) $r->avg_order_value, 2),
            ]);

        return response()->json($rows);
    }

    public function topProducts(Request $request)
    {
        $limit = $request->integer('limit', 10);

        $products = Product::select(['id', 'name', 'sold_count'])
            ->withSum('variants', 'stock')
            ->orderByDesc('sold_count')
            ->limit($limit)
            ->get()
            ->map(fn ($p) => [
                'id'         => $p->id,
                'name'       => $p->name,
                'sold_count' => $p->sold_count,
                'revenue'    => null,
                'stock'      => (int) $p->variants_sum_stock,
            ]);

        return response()->json($products);
    }

    public function lowStock(Request $request)
    {
        $threshold = $request->integer('threshold', 5);

        $variants = DB::table('product_variants')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->where('product_variants.stock', '<=', $threshold)
            ->select('products.id', 'products.name', DB::raw('SUM(product_variants.stock) as stock'), DB::raw('0 as sold_count'), DB::raw('0 as revenue'))
            ->groupBy('products.id', 'products.name')
            ->orderBy('stock')
            ->limit(50)
            ->get();

        return response()->json($variants);
    }

    public function topCustomers(Request $request)
    {
        $limit = $request->integer('limit', 10);

        $customers = User::where('role', 'customer')
            ->withCount('orders')
            ->withSum('orders', 'total_amount')
            ->orderByDesc('orders_sum_total_amount')
            ->limit($limit)
            ->get()
            ->map(fn ($u) => [
                'id'           => $u->id,
                'full_name'    => $u->full_name,
                'email'        => $u->email,
                'orders_count' => $u->orders_count,
                'total_spent'  => (float) $u->orders_sum_total_amount,
            ]);

        return response()->json($customers);
    }

    public function inventoryValue()
    {
        $total = DB::table('product_variants')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->sum(DB::raw('product_variants.stock * products.price'));

        $byCategory = DB::table('product_variants')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->select('categories.name', DB::raw('SUM(product_variants.stock * products.price) as value'))
            ->groupBy('categories.name')
            ->get();

        return response()->json(['total_value' => (float) $total, 'by_category' => $byCategory]);
    }
}
