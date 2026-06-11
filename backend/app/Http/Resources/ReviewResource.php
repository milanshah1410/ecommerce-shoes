<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'title' => $this->title,
            'review' => $this->review,
            'status' => $this->status,
            'user_name' => $this->whenLoaded('user', fn () => $this->user->full_name),
            'created_at' => $this->created_at?->toDateString(),
        ];
    }
}
