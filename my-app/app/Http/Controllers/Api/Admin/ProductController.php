<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /* ================= INDEX ================= */

    public function index()
    {
        $products = Product::with(['category', 'units'])
            ->latest()
            ->get();

        return response()->json($products);
    }

    /* ================= STORE ================= */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'category_id'  => 'required|exists:categories,id',
            'unit_id'      => 'required|exists:units,id',
        ]);

        $product = Product::create($validated);

        return response()->json($product->load(['category', 'units']), 201);
    }

    /* ================= SHOW ================= */

    public function show(Product $product)
    {
        return response()->json(
            $product->load(['category', 'units'])
        );
    }

    /* ================= UPDATE ================= */

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'product_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products')->ignore($product->id),
            ],
            'category_id'  => 'required|exists:categories,id',
            'unit_id'      => 'required|exists:units,id',
        ]);

        $product->update($validated);

        return response()->json(
            $product->load(['category', 'units'])
        );
    }

    /* ================= DELETE ================= */

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully'
        ]);
    }

    /* ================= TOGGLE STATUS ================= */

    public function toggleStatus(Product $product)
    {
        $product->is_active = !$product->is_active;
        $product->save();

        return response()->json([
            'message' => 'Product status updated',
            'is_active' => $product->is_active
        ]);
    }


        public function units($id)
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
