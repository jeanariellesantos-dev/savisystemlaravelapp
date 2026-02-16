<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    //
    // Admin



    //Users
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
                'role' => $user->role?->role_name ?? null,
                'is_active' => $user->is_active,
            ];
        })
    );
}


public function toggleStatus(User $user)
{
    if ($user->id === auth()->id()) {
    return response()->json([
        'message' => 'You cannot deactivate your own account'
    ], 403);
    }

    $user->is_active = !$user->is_active;
    $user->save();
    return response()->json(['message' => 'Status updated']);
}


public function updateUser(Request $request, User $user)
{
    $validated = $request->validate([
        'firstname' => 'required|string|max:255',
        'lastname'  => 'required|string|max:255',
        'email'     => 'required|email|max:255|unique:users,email,' . $user->id,
        'role_id'   => 'required|exists:roles,id',
        'password'  => 'nullable|min:8',
    ]);

    // Update basic info
    $user->firstname = $validated['firstname'];
    $user->lastname  = $validated['lastname'];
    $user->email     = $validated['email'];
    $user->role_id   = $validated['role_id'];

    // Only update password if provided
    if (!empty($validated['password'])) {
        $user->password = Hash::make($validated['password']);
    }

    $user->save();

    // Reload role relationship
    $user->load('role');

    return response()->json([
        'message' => 'User updated successfully',
        'user' => [
            'id' => $user->id,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'email' => $user->email,
            'role' => $user->role?->role_name,
            'is_active' => $user->is_active,
        ]
    ]);
}




}
