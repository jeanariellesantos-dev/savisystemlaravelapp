<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;


class UserController extends Controller
{
    /* ======================================================
     * LIST USERS
     * ====================================================== */
    public function index()
    {
        $users = User::with('role')
            ->select('id','employee_number','firstname','lastname','email','mobile','is_active','role_id')
            ->get();

        return response()->json(
            $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'employee_number' => $user->employee_number,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
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
            'employee_number' => 'required|string|max:255',
            'firstname' => 'required|string|max:255',
            'lastname'  => 'required|string|max:255',
            'email'     => 'email',
            'role_id'   => 'required|exists:roles,id',
            'password'  => 'required|min:8',
        ]);

        $user = User::create([
            'employee_number' => $validated['employee_number'],
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
            // 'user' => [
            //     'id' => $user->id,
            //     'employee_number' => $user->employee_number,
            //     'firstname' => $user->firstname,
            //     'lastname' => $user->lastname,
            //     'email' => $user->email,
            //     'role_id' => $user->role_id,
            //     'role' => $user->role?->role_name,
            //     'is_active' => $user->is_active,
            // ]
        ], 201);
    }

    /* ======================================================
     * UPDATE USER
     * ====================================================== */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'employee_number' => [
                'required',
                'string',
                Rule::unique('users','employee_number')->ignore($user->id),
            ],
            'firstname' => ['required','string','max:255'],
            'lastname'  => ['required','string','max:255'],

            'email' => [
                'email',
            ],

            'role_id' => ['required','exists:roles,id'],
            'password' => ['nullable','min:8'],
        ]);

        $user->employee_number = $validated['employee_number'];
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
            // 'user' => $user
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
