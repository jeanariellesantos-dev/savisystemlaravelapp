<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\InventoryMovement;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class InventoryController extends Controller
{
    //
public function index(Request $request)
{

       $query = DB::table('inventory_movements as im')
        ->leftJoin('products as p', 'im.product_id', '=', 'p.id')
        ->leftJoin('dealerships as d', 'im.dealership_id', '=', 'd.id')
        ->leftJoin('users as u', 'im.created_by', '=', 'u.id')
        ->leftJoin('units as un', 'im.unit_id', '=', 'un.id')

        ->select(
            'im.id',
            'p.product_name as product',
            'un.name as unit',
            'd.dealership_name as dealership',
            'im.type',
            'im.quantity',
            'im.remarks',
            'u.firstname as user',
            'im.created_at'
        );

        // 🔍 Filters
        if ($request->dealership_id) {
            $query->where('im.dealership_id', $request->dealership_id);
        }

        if ($request->product_id) {
            $query->where('im.product_id', $request->product_id);
        }

        if ($request->type) {
            $query->where('im.type', $request->type);
        }

        if ($request->start_date && $request->end_date) {
            $start = Carbon::parse($request->start_date)->startOfDay();
            $end = Carbon::parse($request->end_date)->endOfDay();

            $query->whereBetween('im.created_at', [$start, $end]);
        }

        // 🔽 Latest first
        $query->orderByDesc('im.created_at');
        $query->orderBy('p.product_name', 'asc');

        return response()->json(
            $query->paginate(10)
        );

     }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'dealership_id' => ['required', 'exists:dealerships,id'],
            'type' => ['required', 'string'],
            'unit_id' => ['required', 'exists:units,id'],
            'quantity' => ['required', 'integer'],
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
                $adjustment =$qty; 
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
                'unit_id' => $validated['unit_id'],
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

    public function reverse($id)
{
    DB::beginTransaction();

    try {
        $movement = DB::table('inventory_movements')->where('id', $id)->first();

        if (!$movement) {
            return response()->json([
                'message' => 'Movement not found'
            ], 404);
        }

        // 🔹 Get latest stock
        $last = DB::table('inventory_movements')
            ->where('product_id', $movement->product_id)
            ->where('dealership_id', $movement->dealership_id)
            ->latest('id')
            ->first();

        // ✅ Get or create stock record
        $product = Product::findOrFail($movement->product_id);

        $currentStock = $last->ending_balance ?? 0;

        // 🔹 Reverse quantity
        $reverseQty = -1 * $movement->quantity;

        // 🚨 Prevent negative stock
        if ($currentStock + $reverseQty < 0) {
            return response()->json([
                'message' => 'Cannot reverse, stock will be negative'
            ], 422);
        }

        $ending = $currentStock + $reverseQty;

        // 🔹 Insert reversal record (IMPORTANT: do NOT delete original)
        DB::table('inventory_movements')->insert([
            'product_id' => $movement->product_id,
            'dealership_id' => $movement->dealership_id,
            'unit_id' => $movement->unit_id,
            'type' => 'ADJUSTMENT',
            'quantity' => $reverseQty,
            'starting_balance' => $movement->ending_balance,
            'ending_balance' => $ending,
            'remarks' => 'Reversal of movement #' . $movement->id,
            'reference_type' => 'Reversal of admin',
            'reference_id' => $movement->id,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ✅ Update stock
        $product->update([
            'stock' => $ending
        ]);

        DB::commit();

        return response()->json([
            'message' => 'Movement reversed successfully',
            'ending_balance' => $ending,
        ]);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'message' => 'Failed to reverse movement',
            'error' => $e->getMessage()
        ], 500);
    }
    }

}
