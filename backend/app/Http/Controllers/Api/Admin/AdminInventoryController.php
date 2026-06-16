<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminInventoryController extends Controller
{
    public function lowStock(Request $request)
    {
        $threshold = $request->integer('threshold', 5);

        return response()->json(
            ProductVariant::with('product:id,name')
                ->where('stock', '<=', $threshold)
                ->orderBy('stock')
                ->paginate($request->integer('per_page', 20))
        );
    }

    public function movements(Request $request)
    {
        // Return from stock_movements table if it exists, else fallback
        if (! DB::getSchemaBuilder()->hasTable('stock_movements')) {
            return response()->json(['data' => [], 'meta' => ['total' => 0, 'current_page' => 1, 'last_page' => 1, 'per_page' => 20, 'from' => 0, 'to' => 0]]);
        }

        return response()->json(
            DB::table('stock_movements')
                ->join('product_variants', 'product_variants.id', '=', 'stock_movements.product_variant_id')
                ->join('products', 'products.id', '=', 'product_variants.product_id')
                ->select('stock_movements.*', 'products.name as product_name', 'product_variants.size', 'product_variants.color')
                ->latest('stock_movements.created_at')
                ->paginate($request->integer('per_page', 20))
        );
    }

    public function stockIn(Request $request)
    {
        $data = $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'quantity'   => 'required|integer|min:1',
            'note'       => 'nullable|string|max:255',
        ]);

        return $this->adjustStock($data, 'stock_in');
    }

    public function stockOut(Request $request)
    {
        $data = $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'quantity'   => 'required|integer|min:1',
            'note'       => 'nullable|string|max:255',
        ]);

        $variant = ProductVariant::findOrFail($data['variant_id']);

        if ($variant->stock < $data['quantity']) {
            return response()->json(['message' => 'Insufficient stock.'], 422);
        }

        return $this->adjustStock($data, 'stock_out');
    }

    public function adjust(Request $request)
    {
        $data = $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'new_stock'  => 'required|integer|min:0',
            'note'       => 'nullable|string|max:255',
        ]);

        return $this->adjustStock($data, 'adjustment');
    }

    private function adjustStock(array $data, string $type): \Illuminate\Http\JsonResponse
    {
        $variant = ProductVariant::findOrFail($data['variant_id']);
        $previous = $variant->stock;

        if ($type === 'stock_in') {
            $newStock = $previous + $data['quantity'];
            $qty = $data['quantity'];
        } elseif ($type === 'stock_out') {
            $newStock = $previous - $data['quantity'];
            $qty = $data['quantity'];
        } else {
            $newStock = $data['new_stock'];
            $qty = abs($newStock - $previous);
        }

        $variant->update(['stock' => $newStock]);

        $movement = [
            'type'               => $type,
            'quantity'           => $qty,
            'previous_stock'     => $previous,
            'new_stock'          => $newStock,
            'note'               => $data['note'] ?? null,
            'product_variant_id' => $variant->id,
            'created_by'         => auth()->id(),
        ];

        if (DB::getSchemaBuilder()->hasTable('stock_movements')) {
            DB::table('stock_movements')->insert(array_merge($movement, ['created_at' => now(), 'updated_at' => now()]));
        }

        return response()->json(array_merge($movement, ['variant' => $variant]));
    }
}
