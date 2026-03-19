<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{
    /* =========================
        GET ALL ROLES
    ========================== */
    public function index()
    {
        $roles = Role::orderBy('role_name', 'asc')->get();

        return response()->json($roles);
    }

    /* =========================
        STORE ROLE
    ========================== */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'role_name' => 'required|string|max:255|unique:roles,role_name',
            'role_description' => 'nullable|string',
        ]);

        $role = Role::create([
            'role_name' => $validated['role_name'],
            'role_description' => $validated['role_description'] ?? null,
            'is_active' => true,
        ]);

        return response()->json($role, 201);
    }

    /* =========================
        SHOW ROLE
    ========================== */
    public function show($id)
    {
        $role = Role::findOrFail($id);

        return response()->json($role);
    }

    /* =========================
        UPDATE ROLE
    ========================== */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'role_name' => 'required|string|max:255|unique:roles,role_name,' . $role->id,
            'role_description' => 'nullable|string',
        ]);

        $role->update($validated);

        return response()->json($role);
    }

    /* =========================
        TOGGLE STATUS
    ========================== */
    public function toggle($id)
    {
        $role = Role::findOrFail($id);

        $role->is_active = !$role->is_active;
        $role->save();

        return response()->json([
            'message' => 'Role status updated successfully',
            'role' => $role
        ]);
    }

    /* =========================
        DELETE (Optional)
    ========================== */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        $role->delete();

        return response()->json([
            'message' => 'Role deleted successfully'
        ]);
    }
}
