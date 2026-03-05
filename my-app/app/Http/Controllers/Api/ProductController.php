<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
        ]);

        return response()->json(
            Product::where('category_id', $request->category_id)
                ->where('is_active', true)
                ->select('id', 'product_name')
                ->orderBy('product_name')
                ->get()
        );
    }

    public function show(Product $product)
    {
        return response()->json([
            'id' => $product->id,
            'product_name' => $product->product_name,
            'description' => $product->description,
            'default_unit' => $product->units()
                ->wherePivot('is_default', true)
                ->select('units.id', 'units.name', 'units.abbreviation')
                ->first(),
        ]);
    }

 // GET /api/products/{id}/units

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
