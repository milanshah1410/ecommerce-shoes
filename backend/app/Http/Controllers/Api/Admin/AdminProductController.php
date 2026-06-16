<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category:id,name', 'brand:id,name', 'images']);

        if ($search = $request->search) {
            $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%"));
        }

        if ($status = $request->status) {
            $query->where('status', $status);
        }

        if ($category = $request->category_id) {
            $query->where('category_id', $category);
        }

        return response()->json(
            $query->latest()->paginate($request->integer('per_page', 15))
        );
    }

    public function show(Product $product)
    {
        return response()->json(
            $product->load(['category', 'brand', 'images', 'variants'])
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'              => 'required|string|max:255',
            'slug'              => 'nullable|string|max:255|unique:products',
            'sku'               => 'required|string|max:100|unique:products',
            'category_id'       => 'required|exists:categories,id',
            'brand_id'          => 'required|exists:brands,id',
            'gender'            => 'nullable|string',
            'short_description' => 'nullable|string',
            'description'       => 'nullable|string',
            'price'             => 'required|numeric|min:0',
            'sale_price'        => 'nullable|numeric|min:0',
            'weight'            => 'nullable|numeric|min:0',
            'video_url'         => 'nullable|url',
            'status'            => 'required|in:active,inactive,draft',
            'is_featured'       => 'boolean',
            'is_trending'       => 'boolean',
            'is_new_arrival'    => 'boolean',
            'is_best_seller'    => 'boolean',
            'variants'          => 'nullable|array',
            'images'            => 'nullable|array',
            'images.*'          => 'image|max:4096',
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        $product = Product::create($data);

        $this->syncVariants($product, $request->input('variants', []));
        $this->storeImages($product, $request->file('images', []));

        return response()->json($product->load(['images', 'variants']), 201);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'              => 'sometimes|string|max:255',
            'slug'              => "sometimes|string|max:255|unique:products,slug,{$product->id}",
            'sku'               => "sometimes|string|max:100|unique:products,sku,{$product->id}",
            'category_id'       => 'sometimes|exists:categories,id',
            'brand_id'          => 'sometimes|exists:brands,id',
            'gender'            => 'nullable|string',
            'short_description' => 'nullable|string',
            'description'       => 'nullable|string',
            'price'             => 'sometimes|numeric|min:0',
            'sale_price'        => 'nullable|numeric|min:0',
            'weight'            => 'nullable|numeric|min:0',
            'video_url'         => 'nullable|url',
            'status'            => 'sometimes|in:active,inactive,draft',
            'is_featured'       => 'boolean',
            'is_trending'       => 'boolean',
            'is_new_arrival'    => 'boolean',
            'is_best_seller'    => 'boolean',
            'variants'          => 'nullable|array',
            'images'            => 'nullable|array',
            'images.*'          => 'image|max:4096',
        ]);

        $product->update($data);

        if ($request->has('variants')) {
            $product->variants()->delete();
            $this->syncVariants($product, $request->input('variants', []));
        }

        $this->storeImages($product, $request->file('images', []));

        return response()->json($product->load(['images', 'variants']));
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json(['message' => 'Deleted.']);
    }

    public function deleteImage(Product $product, ProductImage $image)
    {
        abort_unless($image->product_id === $product->id, 404);
        $image->delete();

        return response()->json(['message' => 'Image deleted.']);
    }

    private function syncVariants(Product $product, array $variants): void
    {
        foreach ($variants as $v) {
            ProductVariant::create([
                'product_id' => $product->id,
                'size'       => $v['size'],
                'color'      => $v['color'],
                'stock'      => $v['stock'] ?? 0,
                'sku'        => $v['sku'] ?? null,
            ]);
        }
    }

    private function storeImages(Product $product, array $files): void
    {
        $isPrimary = $product->images()->count() === 0;
        foreach ($files as $file) {
            $path = $file->store("products/{$product->id}", 'public');
            ProductImage::create([
                'product_id' => $product->id,
                'image'      => "/storage/{$path}",
                'is_primary' => $isPrimary,
                'sort_order' => $product->images()->count(),
            ]);
            $isPrimary = false;
        }
    }
}
