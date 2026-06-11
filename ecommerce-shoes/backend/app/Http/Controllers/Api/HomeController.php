<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Http\Resources\ProductResource;
use App\Models\Brand;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $with = ['brand', 'images', 'variants'];
        $base = fn () => Product::active()->with($with);

        return response()->json([
            'trending' => ProductResource::collection($base()->where('is_trending', true)->limit(8)->get()),
            'new_arrivals' => ProductResource::collection($base()->where('is_new_arrival', true)->latest()->limit(8)->get()),
            'best_sellers' => ProductResource::collection($base()->where('is_best_seller', true)->orderByDesc('sold_count')->limit(8)->get()),
            'featured' => ProductResource::collection($base()->where('is_featured', true)->limit(8)->get()),
            'mens' => ProductResource::collection($base()->where('gender', 'men')->limit(8)->get()),
            'womens' => ProductResource::collection($base()->where('gender', 'women')->limit(8)->get()),
            'on_sale' => ProductResource::collection($base()->whereNotNull('sale_price')->limit(8)->get()),
            'brands' => BrandResource::collection(Brand::where('status', 'active')->limit(12)->get()),
        ]);
    }
}
