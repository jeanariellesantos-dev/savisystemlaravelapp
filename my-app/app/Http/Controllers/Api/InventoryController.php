<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\InventoryMovement;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;

class InventoryController extends Controller
{
    //
    public function index(Request $request)
{
    $search = $request->input('search');
    $perPage = $request->input('per_page', 10);
    $dealershipId = $request->input('dealership_id');

    $query = DB::table('products')
        ->leftJoin('inventory_movements as im', function ($join) use ($dealershipId) {
            $join->on('products.id', '=', 'im.product_id')
                 ->where('im.type', 'OUT');

            // ✅ apply dealership filter inside join (IMPORTANT)
            if (!empty($dealershipId)) {
                $join->where('im.dealership_id', $dealershipId);
            }
        })
        ->select(
            'products.id',
            'products.product_name',
            'products.stock',
            DB::raw('MAX(im.created_at) as last_ordered_at')
        );

        // ✅ Search
        if ($search) {
            $query->where('products.product_name', 'like', "%{$search}%");
        }

        // ✅ Grouping (NO dealership_id here)
        $query->groupBy('products.id', 'products.product_name', 'products.stock');

        // ✅ Order (nulls last automatically)
        $query->orderByDesc(DB::raw('MAX(im.created_at)'));
        
        $query->orderBy('products.product_name', 'asc');

        // ✅ Pagination
        $products = $query->paginate($perPage);

        return response()->json($products);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'dealership_id' => ['required', 'exists:dealerships,id'],
            'type' => ['required', 'string'],
            'quantity' => ['required', 'integer', 'min:1'],
            'remarks' => ['nullable', 'string']
        ]);

        // Get user
        $user = auth()->user();

        // ✅ Get or create stock record
        $product = Product::findOrFail($validated['product_id']);

        return DB::transaction(function () use ($validated,  $product,$user) {

            $starting = $product->stock;
            $qty = $validated['quantity'];
            $type = $validated['type'];

            // ✅ Compute ending balance
            if ($type === 'IN') {
                $ending = $starting + $qty;

            } elseif ($type === 'OUT') {

                // ❌ Prevent negative stock
                if ($starting < $qty) {
                    throw ValidationException::withMessages([
                        'stock' => [
                            'Insufficient stock',
                            'available' => $starting,
                            'requested' => $qty,
                            'shortage' => $qty - $starting
                        ]
                    ]);
                }

                $ending = $starting - $qty;

            } else { // ADJUSTMENT

                // 👉 adjustment can be positive or negative
                $adjustment =$validated->input('adjustment', $qty); 
                $ending = $starting + $adjustment;

                if ($ending < 0) {
                    throw ValidationException::withMessages([
                        'stock' => ['Adjustment would result in negative stock']
                    ]);
                }

                $qty = $adjustment; // store signed value
            }

            // ✅ Update stock
            $product->update([
                'stock' => $ending
            ]);

            // ✅ Create movement
            $movement = InventoryMovement::create([
                'product_id' => $validated['product_id'],
                'dealership_id' => $validated['dealership_id'],
                'type' => $type,
                'quantity' => $qty,
                'starting_balance' => $starting,
                'ending_balance' => $ending,
                'reference_type' => 'manual',
                'reference_id' => null,
                'remarks' => $validated['remarks'] ?? null,
                'created_by' => $user->id
            ]);

            return response()->json([
                'message' => 'Inventory movement recorded successfully',
                'data' => $movement
            ], 201);
        });
    }

}
