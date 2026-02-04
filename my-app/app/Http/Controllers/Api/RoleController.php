<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * List all roles
     */
    public function index()
    {
        return Role::orderBy('role_name')->get();
    }

    /**
     * Store a new role
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'role_name'        => 'required|string|max:100|unique:roles,role_name',
            'role_description' => 'required|string|max:255',
        ]);

        $role = Role::create($validated);

        return response()->json([
            'message' => 'Role created successfully',
            'data' => $role,
        ], 201);
    }

    /**
     * Show a single role
     */
    public function show($id)
    {
        return Role::findOrFail($id);
    }

    /**
     * Update role
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'role_name'        => 'required|string|max:100|unique:roles,role_name,' . $role->id,
            'role_description' => 'required|string|max:255',
        ]);

        $role->update($validated);

        return response()->json([
            'message' => 'Role updated successfully',
            'data' => $role,
        ]);
    }

    /**
     * Delete role (use carefully)
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        // Optional safety check
        // if ($role->users()->exists()) {
        //     abort(422, 'Role is assigned to users');
        // }

        $role->delete();

        return response()->json([
            'message' => 'Role deleted successfully',
        ]);
    }
}
