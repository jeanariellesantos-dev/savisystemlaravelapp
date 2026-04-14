<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Unit;
use App\Models\Product;


class UnitController extends Controller
{
    /* =========================
       GET ALL UNITS
    ========================= */
    public function index(Request $request)
    {
        $query = Unit::query();

        /* ================= SEARCH ================= */
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        /* ================= SORT ================= */
        $query->orderBy('name', 'asc');

        /* ================= PAGINATION ================= */
        $perPage = $request->get('per_page', 10);

        $units = $query
            ->paginate($perPage)
            ->appends($request->all());

        return response()->json($units);
    }

    /* =========================
       CREATE UNIT
    ========================= */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:units,name',
            'abbreviation'=>'required|string|max:255|unique:units,abbreviation',
        ]);

        $unit = Unit::create([
            'name' => $validated['name'],
            'abbreviation'=> $validated['abbreviation'],
            'is_active' => true,
        ]);

        return response()->json($unit, 201);
    }

    /* =========================
       UPDATE UNIT
    ========================= */
    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:units,name',
            'abbreviation'=>'required|string|max:255|unique:units,abbreviation',
        ]);

        $unit->update([
            'name' => $validated['name'],
            'abbreviation'=> $validated['abbreviation'],
        ]);

        return response()->json($unit);
    }

    /* =========================
       DELETE UNIT
    ========================= */
    public function destroy(Unit $unit)
    {
        $unit->delete();

        return response()->json([
            'message' => 'Unit deleted successfully'
        ]);
    }

    /* =========================
       TOGGLE ACTIVE (RECOMMENDED)
    ========================= */
    public function toggle(Unit $unit)
    {
        $unit->is_active = !$unit->is_active;
        $unit->save();

        return response()->json($unit);
    }

    public function getByProductId($id)
    {
        $product = Product::with('units:id,name,abbreviation')
            ->findOrFail($id);

        return response()->json(
            $product->units->map(fn ($unit) => [
                'id'   => $unit->id,
                'name' => $unit->name,
            ])
        );
    }
}
