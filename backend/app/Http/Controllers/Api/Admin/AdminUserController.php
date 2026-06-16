<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($search = $request->search) {
            $query->where(fn ($q) => $q
                ->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"));
        }

        if ($role = $request->role) {
            $query->where('role', $role);
        }

        return response()->json(
            $query->latest()->paginate($request->integer('per_page', 15))
        );
    }

    public function show(User $user)
    {
        return response()->json($user);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'nullable|string|max:100',
            'email'      => 'required|email|unique:users',
            'mobile'     => 'nullable|string|max:20',
            'role'       => ['required', Rule::in(['super_admin','admin','manager','inventory_manager','sales_manager','customer_support','customer'])],
            'password'   => 'required|string|min:8',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['status']   = 'active';

        return response()->json(User::create($data), 201);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'first_name' => 'sometimes|string|max:100',
            'last_name'  => 'nullable|string|max:100',
            'email'      => ['sometimes','email', Rule::unique('users')->ignore($user->id)],
            'mobile'     => 'nullable|string|max:20',
            'role'       => ['sometimes', Rule::in(['super_admin','admin','manager','inventory_manager','sales_manager','customer_support','customer'])],
            'password'   => 'nullable|string|min:8',
        ]);

        if (isset($data['password']) && $data['password']) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json($user->fresh());
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(['message' => 'Deleted.']);
    }

    public function toggleStatus(User $user)
    {
        $user->update(['status' => $user->status === 'active' ? 'blocked' : 'active']);

        return response()->json($user);
    }
}
