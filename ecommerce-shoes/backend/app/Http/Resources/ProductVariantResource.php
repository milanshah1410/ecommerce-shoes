<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'size' => $this->size,
            'color' => $this->color,
            'color_hex' => $this->color_hex,
            'price_adjustment' => (float) $this->price_adjustment,
            'stock' => $this->stock,
            'in_stock' => $this->stock > 0,
        ];
    }
}
