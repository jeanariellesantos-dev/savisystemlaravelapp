<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /* ======================================================
     * LIST USERS
     * ====================================================== */
    public function index()
    {
        $users = User::with('role')
            ->select('id','firstname','lastname','email','is_active','role_id')
            ->get();

        return response()->json(
            $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'email' => $user->email,
                    'role_id' => $user->role_id,
                    'role' => $user->role?->role_name ?? null,
                    'is_active' => $user->is_active,
                ];
            })
        );
    }

    /* ======================================================
     * CREATE USER
     * ====================================================== */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname'  => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'role_id'   => 'required|exists:roles,id',
            'password'  => 'required|min:8',
        ]);

        $user = User::create([
            'firstname' => $validated['firstname'],
            'lastname'  => $validated['lastname'],
            'email'     => $validated['email'],
            'role_id'   => $validated['role_id'],
            'password'  => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        $user->load('role');

        return response()->json([
            'message' => 'User created successfully',
            'user' => [
                'id' => $user->id,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'email' => $user->email,
                'role_id' => $user->role_id,
                'role' => $user->role?->role_name,
                'is_active' => $user->is_active,
            ]
        ], 201);
    }

    /* ======================================================
     * UPDATE USER
     * ====================================================== */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname'  => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:users,email,' . $user->id,
            'role_id'   => 'required|exists:roles,id',
            'password'  => 'nullable|min:8',
        ]);

        $user->firstname = $validated['firstname'];
        $user->lastname  = $validated['lastname'];
        $user->email     = $validated['email'];
        $user->role_id   = $validated['role_id'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();
        $user->load('role');

        return response()->json([
            'message' => 'User updated successfully',
            'user' => [
                'id' => $user->id,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'email' => $user->email,
                'role_id' => $user->role_id,
                'role' => $user->role?->role_name,
                'is_active' => $user->is_active,
            ]
        ]);
    }

    /* ======================================================
     * TOGGLE STATUS
     * ====================================================== */
    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'You cannot deactivate your own account'
            ], 403);
        }

        $user->is_active = !$user->is_active;
        $user->save();

        return response()->json([
            'message' => 'Status updated',
            'is_active' => $user->is_active
        ]);
    }
}
