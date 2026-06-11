<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:150'],
            'review' => ['nullable', 'string', 'max:2000'],
        ]);

        $review = Review::updateOrCreate(
            ['user_id' => $request->user()->id, 'product_id' => $product->id],
            [...$data, 'status' => 'pending'],
        );

        $this->recalculateProductRating($product);

        return response()->json([
            'message' => 'Thank you! Your review will appear once approved.',
            'data' => new ReviewResource($review->load('user')),
        ], 201);
    }

    private function recalculateProductRating(Product $product): void
    {
        $approved = $product->reviews()->where('status', 'approved');
        $product->update([
            'rating' => round((float) $approved->avg('rating'), 2),
            'reviews_count' => $approved->count(),
        ]);
    }
}
