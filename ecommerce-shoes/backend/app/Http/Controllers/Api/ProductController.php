<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ReviewResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()
            ->active()
            ->with(['brand', 'category', 'images', 'variants']);

        // Filters
        if ($request->filled('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
        }
        if ($request->filled('brand')) {
            $query->whereHas('brand', fn ($q) => $q->where('slug', $request->brand));
        }
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }
        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->max_price);
        }
        if ($request->filled('rating')) {
            $query->where('rating', '>=', (float) $request->rating);
        }
        if ($request->boolean('on_sale')) {
            $query->whereNotNull('sale_price');
        }
        if ($request->filled('size')) {
            $query->whereHas('variants', fn ($q) => $q->where('size', $request->size)->where('stock', '>', 0));
        }
        if ($request->filled('color')) {
            $query->whereHas('variants', fn ($q) => $q->where('color', $request->color));
        }
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")
                ->orWhere('short_description', 'like', "%{$term}%"));
        }
        foreach (['featured' => 'is_featured', 'trending' => 'is_trending', 'new' => 'is_new_arrival', 'best_seller' => 'is_best_seller'] as $flag => $column) {
            if ($request->boolean($flag)) {
                $query->where($column, true);
            }
        }

        // Sorting
        match ($request->get('sort')) {
            'price_asc' => $query->orderByRaw('COALESCE(sale_price, price) asc'),
            'price_desc' => $query->orderByRaw('COALESCE(sale_price, price) desc'),
            'best_selling' => $query->orderByDesc('sold_count'),
            'popular' => $query->orderByDesc('reviews_count')->orderByDesc('rating'),
            default => $query->latest(),
        };

        $products = $query->paginate((int) $request->get('per_page', 12))->withQueryString();

        return ProductResource::collection($products);
    }

    public function show(string $slug)
    {
        $product = Product::active()
            ->with(['brand', 'category', 'images', 'variants'])
            ->where('slug', $slug)
            ->firstOrFail();

        $product->setRelation(
            'related',
            Product::active()
                ->with(['brand', 'images', 'variants'])
                ->where('category_id', $product->category_id)
                ->whereKeyNot($product->id)
                ->limit(4)
                ->get()
        );

        return response()->json([
            'data' => new ProductResource($product),
            'related' => ProductResource::collection($product->related),
            'reviews' => ReviewResource::collection(
                $product->reviews()->where('status', 'approved')->with('user')->latest()->limit(20)->get()
            ),
        ]);
    }
}
