<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dealership;
use Illuminate\Http\Request;

class DealershipController extends Controller
{
    /* ===============================
        GET ALL DEALERSHIPS
    =============================== */
    public function index(Request $request)
    {
        $query = Dealership::query();

        /* ================= SEARCH ================= */
        if ($request->filled('search')) {
            $query->where('dealership_name', 'like', '%' . $request->search . '%');
        }

        /* ================= SORT ================= */
        $query->orderBy('dealership_name', 'asc');

        /* ================= PAGINATION ================= */
        $perPage = $request->get('per_page', 10);

        $dealerships = $query
            ->paginate($perPage)
            ->appends($request->all());

        return response()->json($dealerships);
    }

    /* ===============================
        STORE NEW DEALERSHIP
    =============================== */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'dealership_name' => 'required|string|max:255|unique:dealerships,dealership_name',
            'location'        => 'required|string|max:255',
        ]);

        $dealership = Dealership::create([
            'dealership_name' => $validated['dealership_name'],
            'location'        => $validated['location'],
            'is_active'       => true,
        ]);

        return response()->json($dealership, 201);
    }

    /* ===============================
        SHOW SINGLE DEALERSHIP
    =============================== */
    public function show($id)
    {
        $dealership = Dealership::findOrFail($id);

        return response()->json($dealership);
    }

    /* ===============================
        UPDATE DEALERSHIP
    =============================== */
    public function update(Request $request, $id)
    {
        $dealership = Dealership::findOrFail($id);

        $validated = $request->validate([
            'dealership_name' => 'required|string|max:255|unique:dealerships,dealership_name,' . $dealership->id,
            'location'        => 'required|string|max:255',
        ]);

        $dealership->update($validated);

        return response()->json($dealership);
    }

    /* ===============================
        TOGGLE ACTIVE STATUS
    =============================== */
    public function toggleStatus($id)
    {
        $dealership = Dealership::findOrFail($id);

        $dealership->is_active = !$dealership->is_active;
        $dealership->save();

        return response()->json([
            'message' => 'Dealership status updated',
            'data'    => $dealership
        ]);
    }

    /* ===============================
        DELETE (Optional – If Needed)
    =============================== */
    public function destroy($id)
    {
        $dealership = Dealership::findOrFail($id);
        $dealership->delete();

        return response()->json([
            'message' => 'Dealership deleted successfully'
        ]);
    }
}
