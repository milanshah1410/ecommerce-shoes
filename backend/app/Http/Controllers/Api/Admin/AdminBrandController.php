<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminBrandController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            Brand::withCount('products')->latest()->paginate($request->integer('per_page', 15))
        );
    }

    public function all()
    {
        return response()->json(Brand::where('status', 'active')->orderBy('name')->get(['id', 'name']));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:100|unique:brands',
            'status' => 'in:active,inactive',
            'logo'   => 'nullable|image|max:2048',
        ]);

        $data['slug']   = Str::slug($data['name']);
        $data['status'] = $data['status'] ?? 'active';

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('brands', 'public');
            $data['logo'] = "/storage/{$path}";
        }

        return response()->json(Brand::create($data), 201);
    }

    public function update(Request $request, Brand $brand)
    {
        $data = $request->validate([
            'name'   => "sometimes|string|max:100|unique:brands,name,{$brand->id}",
            'status' => 'in:active,inactive',
            'logo'   => 'nullable|image|max:2048',
        ]);

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('brands', 'public');
            $data['logo'] = "/storage/{$path}";
        }

        $brand->update($data);

        return response()->json($brand->fresh());
    }

    public function destroy(Brand $brand)
    {
        $brand->delete();

        return response()->json(['message' => 'Deleted.']);
    }
}
