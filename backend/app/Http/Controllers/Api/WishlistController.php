<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Cart;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $items = $request->user()->wishlists()
            ->with(['product.brand', 'product.images', 'product.variants'])
            ->latest()
            ->get()
            ->pluck('product')
            ->filter();

        return ProductResource::collection($items);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
        ]);

        $request->user()->wishlists()->firstOrCreate(['product_id' => $data['product_id']]);

        return response()->json(['message' => 'Added to wishlist.'], 201);
    }

    public function destroy(Request $request, int $productId)
    {
        $request->user()->wishlists()->where('product_id', $productId)->delete();

        return response()->json(['message' => 'Removed from wishlist.']);
    }

    public function moveToCart(Request $request, int $productId)
    {
        $request->user()->wishlists()->where('product_id', $productId)->delete();

        Cart::updateOrCreate(
            ['user_id' => $request->user()->id, 'product_id' => $productId, 'variant_id' => null],
            ['quantity' => 1],
        );

        return response()->json(['message' => 'Moved to cart.']);
    }
}
