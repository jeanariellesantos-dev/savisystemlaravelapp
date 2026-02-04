<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dealership;
use Illuminate\Http\Request;

class DealershipController extends Controller
{
    /**
     * List all dealerships
     */
    public function index()
    {
        return Dealership::orderBy('dealership_name')->get();
    }

    /**
     * Store a new dealership
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'dealership_name' => 'required|string|max:255',
            'location'        => 'required|string|max:255',
            'is_active'       => 'nullable|boolean',
        ]);

        $dealership = Dealership::create([
            'dealership_name' => $validated['dealership_name'],
            'location'        => $validated['location'],
            'is_active'       => $validated['is_active'] ?? 1,
        ]);

        return response()->json([
            'message' => 'Dealership created successfully',
            'data' => $dealership,
        ], 201);
    }

    /**
     * Show single dealership
     */
    public function show($id)
    {
        return Dealership::findOrFail($id);
    }

    /**
     * Update dealership
     */
    public function update(Request $request, $id)
    {
        $dealership = Dealership::findOrFail($id);

        $validated = $request->validate([
            'dealership_name' => 'required|string|max:255',
            'location'        => 'required|string|max:255',
            'is_active'       => 'nullable|boolean',
        ]);

        $dealership->update($validated);

        return response()->json([
            'message' => 'Dealership updated successfully',
            'data' => $dealership,
        ]);
    }

    /**
     * Soft deactivate dealership (recommended)
     */
    public function deactivate($id)
    {
        $dealership = Dealership::findOrFail($id);
        $dealership->update(['is_active' => 0]);

        return response()->json([
            'message' => 'Dealership deactivated',
        ]);
    }

    /**
     * Delete dealership (use with caution)
     */
    public function destroy($id)
    {
        Dealership::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Dealership deleted permanently',
        ]);
    }
}
