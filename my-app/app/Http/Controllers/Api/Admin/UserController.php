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
    public function index(Request $request)
    {
        $query = User::with('role')
            ->select(
                'id',
                'employee_number',
                'firstname',
                'lastname',
                'email',
                'mobile',
                'is_active',
                'role_id',
                'dealership_id'
            );

        /* ================= SEARCH ================= */
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                ->orWhere('lastname', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('employee_number', 'like', "%{$search}%");
            });
        }

        /* ================= SORT ================= */
        $query->orderBy('firstname', 'asc');

        /* ================= PAGINATION ================= */
        $perPage = $request->get('per_page', 10);

        $users = $query
            ->paginate($perPage)
            ->appends($request->all())
            ->through(function ($user) {
                return [
                    'id' => $user->id,
                    'employee_number' => $user->employee_number,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'role_id' => $user->role_id,
                    'dealership_id' => $user->dealership_id,
                    'role' => $user->role?->role_name ?? null,
                    'is_active' => $user->is_active,
                ];
            });

        return response()->json($users);
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
            'mobile'     => 'required|string|max:255',
            'role_id'   => 'required|exists:roles,id',
            'dealership_id' => ['required','exists:dealerships,id'],
            'password'  => 'required|min:8',
        ]);

        $user = User::create([
            'employee_number' => $validated['employee_number'],
            'firstname' => $validated['firstname'],
            'lastname'  => $validated['lastname'],
            'email'     => $validated['email'],
            'mobile'     => $validated['mobile'],
            'role_id'   => $validated['role_id'],
            'dealership_id'   => $validated['dealership_id'],
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
            'mobile'     => 'string|max:255',
            'dealership_id' => ['required','exists:dealerships,id'],
            'role_id' => ['required','exists:roles,id'],
            'password' => ['nullable','min:8'],
        ]);

        $user->employee_number = $validated['employee_number'];
        $user->firstname = $validated['firstname'];
        $user->lastname  = $validated['lastname'];
        $user->email     = $validated['email'];
        $user->mobile     = $validated['mobile'];
        $user->dealership_id   = $validated['dealership_id'];
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
