<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminCategoryController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            Category::withCount('products')->latest()->paginate($request->integer('per_page', 15))
        );
    }

    public function all()
    {
        return response()->json(Category::where('status', 'active')->orderBy('name')->get(['id', 'name']));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:100|unique:categories',
            'status' => 'in:active,inactive',
            'image'  => 'nullable|image|max:2048',
        ]);

        $data['slug']   = Str::slug($data['name']);
        $data['status'] = $data['status'] ?? 'active';

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('categories', 'public');
            $data['image'] = "/storage/{$path}";
        }

        return response()->json(Category::create($data), 201);
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name'   => "sometimes|string|max:100|unique:categories,name,{$category->id}",
            'status' => 'in:active,inactive',
            'image'  => 'nullable|image|max:2048',
        ]);

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('categories', 'public');
            $data['image'] = "/storage/{$path}";
        }

        $category->update($data);

        return response()->json($category->fresh());
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return response()->json(['message' => 'Deleted.']);
    }
}
