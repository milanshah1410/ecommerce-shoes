<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'subtotal' => (float) $this->subtotal,
            'tax' => (float) $this->tax,
            'shipping_charge' => (float) $this->shipping_charge,
            'discount' => (float) $this->discount,
            'total_amount' => (float) $this->total_amount,
            'coupon_code' => $this->coupon_code,
            'can_be_cancelled' => $this->canBeCancelled(),
            'shipping' => [
                'full_name' => $this->ship_full_name,
                'mobile' => $this->ship_mobile,
                'address_line' => $this->ship_address_line,
                'city' => $this->ship_city,
                'state' => $this->ship_state,
                'country' => $this->ship_country,
                'pincode' => $this->ship_pincode,
            ],
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'items_count' => $this->whenCounted('items'),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
