<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class AdminReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with(['user:id,first_name,last_name,name', 'product:id,name']);

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        return response()->json(
            $query->latest()->paginate($request->integer('per_page', 15))
        );
    }

    public function approve(Review $review)
    {
        $review->update(['status' => 'approved']);

        return response()->json($review);
    }

    public function reject(Review $review)
    {
        $review->update(['status' => 'rejected']);

        return response()->json($review);
    }

    public function destroy(Review $review)
    {
        $review->delete();

        return response()->json(['message' => 'Deleted.']);
    }
}
