<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Request as RequestModel;
use App\Models\Approval;
use App\Models\RequestStatusLog;
use App\Models\Product;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApprovalController extends Controller
{
    public function approve(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:APPROVED,REJECTED,ON_HOLD,CANCELLED',
            'remarks' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.unit_id' => 'required_with:items|exists:units,id',
            'items.*.quantity' => 'required_with:items|integer|min:1'
        ]);

        $req = RequestModel::findOrFail($id);
        $user = auth()->user();
        $roleName= $user->role->role_name;

        // 🔒 Ensure admin only
        abort_unless($roleName === 'ADMINISTRATOR', 403);

        /*
        |--------------------------------------------------------------------------
        | FLOW (same as normal)
        |--------------------------------------------------------------------------
        */
        $flow = [
            'ACCOUNTING' => 'PENDING_SUPERVISOR',
            'SUPERVISOR' => 'PENDING_CLUSTER_HEAD',
            'CLUSTER_HEAD' => 'PENDING_INVENTORY',
            'INVENTORY' => 'SHIPPED',
            'OPERATION' => 'RECEIVED',
        ];

        /*
        |--------------------------------------------------------------------------
        | RESOLVE ROLE FROM STATUS
        |--------------------------------------------------------------------------
        */
        $effectiveRole = match ($req->status) {
            'PENDING_ACCOUNTING' => 'ACCOUNTING',
            'PENDING_SUPERVISOR' => 'SUPERVISOR',
            'PENDING_CLUSTER_HEAD' => 'CLUSTER_HEAD',
            'PENDING_INVENTORY' => 'INVENTORY',
            'SHIPPED' => 'OPERATION',
            default => null,
        };

        /*
        |--------------------------------------------------------------------------
        | DETERMINE STATUS
        |--------------------------------------------------------------------------
        */
        if ($request->action === 'REJECTED') {

            $req->status = 'REJECTED';

        } elseif ($request->action === 'ON_HOLD') {

            if ($req->status === 'ON_HOLD') {

                $previousStatus = RequestStatusLog::where('request_id', $req->id)
                    ->where('status', '!=', 'ON_HOLD')
                    ->orderByDesc('created_at')
                    ->value('status');

                abort_if(!$previousStatus, 422, 'Previous status not found');

                $req->status = $previousStatus;

            } else {
                $req->status = 'ON_HOLD';
            }

        } elseif ($request->action === 'CANCELLED') {

            $req->status = 'CANCELLED';

        } else {

            abort_unless(isset($flow[$effectiveRole]), 403);

            $req->status = $flow[$effectiveRole];
        }

        /*
        |--------------------------------------------------------------------------
        | UPDATE ITEMS
        |--------------------------------------------------------------------------
        */
        if ($request->action === 'APPROVED' && $request->has('items') && $req->status === 'PENDING_ACCOUNTING') {

            $insufficientProducts = [];

            foreach ($request->items as $item) {

                $product = Product::find($item['product_id']);

                if ($product->stock < $item['quantity']) {
                    $insufficientProducts[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->product_name,
                        'available_stock' => $product->stock,
                        'requested_quantity' => $item['quantity'],
                        'shortage' => $item['quantity'] - $product->stock,
                    ];
                }
            }

            if (!empty($insufficientProducts)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock',
                    'errors' => [
                        'insufficient_products' => $insufficientProducts
                    ]
                ], 200); // ✅ force 200
            }

            DB::transaction(function () use ($request, $req, $user) {

                $req->items()->delete();
            

                foreach ($request->items as $item) {

                    $starting = 0;
                    $ending = 0;

                    // ✅ ONLY ACCOUNTING computes balances
                    $product = Product::find($item['product_id']);

                    $starting = $product->stock;
                    $ending = $starting - $item['quantity'];

                    $product->update([
                        'stock' => $ending
                    ]);
                    
                    $req->items()->create([
                        'product_id' => $item['product_id'],
                        'unit_id' => $item['unit_id'],
                        'quantity' => $item['quantity'],
                        'starting_balance' => $starting,
                        'ending_balance' => $ending,
                    ]);

                    InventoryMovement::create([
                        'product_id' => $product->id,
                        'dealership_id' => $user->dealership_id,
                        'type' => 'OUT',
                        'quantity' => $item->quantity,
                        'starting_balance' => $starting,
                        'ending_balance' => $ending,
                        'reference_type' => 'request',
                        'reference_id' => $req->id,
                        'created_by' => $user->id
                    ]);
                    
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | REMARKS
        |--------------------------------------------------------------------------
        */
        $actionLabel = match ($request->action) {
            'APPROVED' => 'Approved',
            'REJECTED' => 'Rejected',
            'ON_HOLD' => $req->status === 'ON_HOLD'
                ? 'Put on hold'
                : 'Resumed from hold',
            'CANCELLED' => 'Cancelled',
        };

        $formattedRemarks = "[ADMIN]: {$actionLabel}. " . (
            $request->filled('remarks')
                ? trim($request->remarks)
                : 'No remarks provided'
        );

        Approval::create([
            'request_id' => $req->id,
            'approver_id' => auth()->id(),
            'role_id' => $user->id,
            'action' => $request->action,
            'remarks' => $formattedRemarks
        ]);

        RequestStatusLog::create([
            'request_id' => $req->id,
            'updated_by' => auth()->id(),
            'status' => $req->status
        ]);

        $req->save();

        return response()->json([
            'message' => 'Admin action completed'
        ]);
    }
}