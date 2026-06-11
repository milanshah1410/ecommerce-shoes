<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'total_orders' => $user->orders()->count(),
            'pending_orders' => $user->orders()->whereIn('status', ['pending', 'confirmed', 'processing', 'shipped'])->count(),
            'wishlist_count' => $user->wishlists()->count(),
            'cart_count' => (int) $user->cartItems()->sum('quantity'),
            'total_spent' => round((float) $user->orders()->where('payment_status', 'paid')->sum('total_amount'), 2),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'mobile' => ['nullable', 'string', 'max:20'],
        ]);

        $request->user()->update([
            ...$data,
            'name' => trim($data['first_name'].' '.($data['last_name'] ?? '')),
        ]);

        return new UserResource($request->user()->fresh());
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if (! Hash::check($data['current_password'], $request->user()->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $request->user()->update(['password' => Hash::make($data['password'])]);

        return response()->json(['message' => 'Password updated successfully.']);
    }
}
