<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $primary = $this->whenLoaded('images', fn () => $this->images->firstWhere('is_primary', true) ?? $this->images->first());

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'gender' => $this->gender,
            'short_description' => $this->short_description,
            'price' => (float) $this->price,
            'sale_price' => $this->sale_price !== null ? (float) $this->sale_price : null,
            'effective_price' => $this->effective_price,
            'discount_percent' => $this->discount_percent,
            'rating' => (float) $this->rating,
            'reviews_count' => $this->reviews_count,
            'is_featured' => $this->is_featured,
            'is_trending' => $this->is_trending,
            'is_new_arrival' => $this->is_new_arrival,
            'is_best_seller' => $this->is_best_seller,
            'in_stock' => $this->whenLoaded('variants', fn () => $this->in_stock),
            'thumbnail' => $primary?->image,
            'brand' => new BrandResource($this->whenLoaded('brand')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
        ];
    }
}
