<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Http\Resources\CategoryResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductVariant;

class CatalogController extends Controller
{
    public function categories()
    {
        $categories = Category::where('status', 'active')
            ->whereNull('parent_id')
            ->with('children')
            ->withCount('products')
            ->get();

        return CategoryResource::collection($categories);
    }

    public function brands()
    {
        $brands = Brand::where('status', 'active')
            ->withCount('products')
            ->orderBy('name')
            ->get();

        return BrandResource::collection($brands);
    }

    /**
     * Distinct sizes/colors available across active variants, for filter UI.
     */
    public function filters()
    {
        return response()->json([
            'sizes' => ProductVariant::query()->distinct()->orderBy('size')->pluck('size'),
            'colors' => ProductVariant::query()
                ->select('color', 'color_hex')
                ->distinct()
                ->orderBy('color')
                ->get(),
        ]);
    }
}
