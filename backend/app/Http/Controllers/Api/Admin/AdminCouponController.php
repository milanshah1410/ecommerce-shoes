<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminCouponController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            Coupon::latest()->paginate($request->integer('per_page', 15))
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'              => 'required|string|max:50|unique:coupons|uppercase',
            'type'              => ['required', Rule::in(['percentage', 'fixed'])],
            'discount_value'    => 'required|numeric|min:0.01',
            'min_order_amount'  => 'nullable|numeric|min:0',
            'max_discount'      => 'nullable|numeric|min:0',
            'start_date'        => 'nullable|date',
            'end_date'          => 'nullable|date|after_or_equal:start_date',
            'usage_limit'       => 'nullable|integer|min:1',
            'status'            => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $data['code'] = strtoupper($data['code']);

        return response()->json(Coupon::create($data), 201);
    }

    public function update(Request $request, Coupon $coupon)
    {
        $data = $request->validate([
            'code'             => ["sometimes", "string", "max:50", Rule::unique('coupons')->ignore($coupon->id)],
            'type'             => ['sometimes', Rule::in(['percentage', 'fixed'])],
            'discount_value'   => 'sometimes|numeric|min:0.01',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount'     => 'nullable|numeric|min:0',
            'start_date'       => 'nullable|date',
            'end_date'         => 'nullable|date',
            'usage_limit'      => 'nullable|integer|min:1',
            'status'           => ['sometimes', Rule::in(['active', 'inactive'])],
        ]);

        if (isset($data['code'])) {
            $data['code'] = strtoupper($data['code']);
        }

        $coupon->update($data);

        return response()->json($coupon->fresh());
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return response()->json(['message' => 'Deleted.']);
    }
}
