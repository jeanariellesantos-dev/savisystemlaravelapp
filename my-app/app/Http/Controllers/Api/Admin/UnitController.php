<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Unit;

class UnitController extends Controller
{
    /* =========================
       GET ALL UNITS
    ========================= */
    public function index()
    {
        return response()->json(
            Unit::orderBy('name')->get()
        );
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
}
