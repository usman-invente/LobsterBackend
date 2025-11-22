<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
class UserController extends Controller
{
    public function sidebarMenus()
    {
        return response()->json([
            'menus' => [
                'dashboard',
                'offload',
                'receiving',
                'recheck',
                'tanks',
                'dispatch',
                'reports',
                'losses',
                'settings'
            ]
        ]);
    }

    // Create a new user with permissions
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'permissions' => 'array'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'permissions' => $request->permissions ?? [],
        ]);

        return response()->json(['user' => $user], 201);
    }

    // Show user (with permissions)
    public function show(User $user)
    {
        return response()->json($user);
    }

    // Update user permissions
    public function update(Request $request, User $user)
    {
        $request->validate([
            'permissions' => 'array'
        ]);
        $user->permissions = $request->permissions ?? [];
        $user->save();

        return response()->json(['user' => $user]);
    }

    public function index(Request $request)
    {
        $query = User::query();

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'name');
        $sortDirection = $request->input('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        // Pagination
        $perPage = $request->input('per_page', 10);

        $users = $query->paginate($perPage);

        return response()->json($users);
    }
}
