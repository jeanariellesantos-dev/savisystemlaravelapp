<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /* ================= INDEX ================= */

    public function index(Request $request)
    {
        $query = Product::with(['category', 'units'])
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('products.*');

        /* ===============================
        SEARCH
        =============================== */
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('products.product_name', 'like', "%{$search}%")
                ->orWhere('categories.name', 'like', "%{$search}%");
            });
        }

        /* ===============================
        SORTING
        =============================== */
        $query
            // ->orderBy('categories.name', 'asc')
            ->orderBy('products.product_name', 'asc');

        /* ===============================
        PAGINATION
        =============================== */

        $perPage = $request->get('per_page', 10);

        if ($request->has('page')) {
            $products = $query->paginate($perPage);
        } else {
            $products = [
                'data' => $query->get()
            ];
        }

        return response()->json($products);
    }

    /* ================= STORE ================= */

        public function store(Request $request)
        {
            $validated = $request->validate([
                'product_name' => 'required|string|max:255',
                'category_id'  => 'required|exists:categories,id',
                'stock'        => 'required|integer|min:0',
                'unit_ids'     => 'nullable|array',
                'unit_ids.*'   => 'exists:units,id',
            ]);

            $product = Product::create([
                'product_name' => $validated['product_name'],
                'category_id'  => $validated['category_id'],
                'stock' => $validated['stock'],
                'is_active'    => true,
            ]);

            // Attach units
            if (!empty($validated['unit_ids'])) {
                $product->units()->attach($validated['unit_ids']);
            }

            return response()->json($product->load('category', 'units'), 201);
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
            'product_name' => 'required|string|max:255',
            'category_id'  => 'required|exists:categories,id',
            'stock'        => 'required|integer|min:0',
            'unit_ids'     => 'nullable|array',
            'unit_ids.*'   => 'exists:units,id',
        ]);

        $product->update([
            'product_name' => $validated['product_name'],
            'stock'        => $validated['stock'],
            'category_id'  => $validated['category_id'],
        ]);

        // Sync units (important)
        $product->units()->sync($validated['unit_ids'] ?? []);

        return response()->json(
            $product->load('category', 'units')
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
