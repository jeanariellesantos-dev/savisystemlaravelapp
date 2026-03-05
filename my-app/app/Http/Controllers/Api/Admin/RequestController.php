<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Request as RequestModel;
use App\Models\Product;
use App\Models\RequestStatusLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Http\Requests\AddRequest;
use App\Http\Requests\UpdateRequest;

class RequestController extends Controller
{
  public function index(Request $request)
    {
        $query = RequestModel::with([
            'requestor:id,firstname',
            'items.unit:id,name',
            'items.product:id,category_id,product_name',
            'items.product.category:id,name',
            'approvals:id,request_id,remarks,created_at',
            'shipments:id,request_id,shipped_date,received_date,tracking_link'
        ]);

        /* ===============================
           SEARCH
        =============================== */

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('request_id', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%")
                  ->orWhereHas('requestor', function ($q) use ($search) {
                      $q->where('firstname', 'like', "%{$search}%");
                  });
            });
        }

        /* ===============================
           STATUS FILTER
        =============================== */

        if ($request->filled('status')) {

            if ($request->status === 'active') {

                $query->whereNotIn('status', [
                    'RECEIVED',
                    'REJECTED'
                ]);

            } elseif ($request->status === '') {

                // no filter

            } else {

                $query->where('status', $request->status);

            }
        }

        /* ===============================
           PAGINATION
        =============================== */

        $perPage = $request->get('per_page', 10);

        return response()->json(
            $query
                ->latest()
                ->paginate($perPage)
        );
    }

    public function store(AddRequest $request)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, &$req) {

            $req = RequestModel::create([
                'requestor_id' => $validated['requestor_id'],
                'status' => $validated['status'],
            ]);

            foreach ($validated['items'] as $item) {

                $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                $req->items()->create([
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'],
                    'quantity' => $item['quantity'],
                    'starting_balance' => $product->quantity,
                    'ending_balance' => $product->quantity,
                ]);
            }

            RequestStatusLog::create([
                'request_id' => $req->id,
                'updated_by' => Auth::id(),
                'status' => $req->status,
            ]);
        });

        return response()->json([
            'message' => 'Request created successfully',
            'data' => $req->load('items.product', 'requestor')
        ], 201);
    }

    public function show($id)
    {
        $request = RequestModel::with([
            'requestor',
            'items.product',
            'items.unit',
            'approvals',
            'shipments'
        ])->findOrFail($id);

        return response()->json($request);
    }

    public function update(UpdateRequest $request, $id)
    {
        $req = RequestModel::findOrFail($id);

        $req->update($request->validated());

        return response()->json([
            'message' => 'Request updated successfully',
            'data' => $req->load('items.product')
        ]);
    }

    public function destroy($id)
    {
        $req = RequestModel::findOrFail($id);

        $req->delete();

        return response()->json([
            'message' => 'Request deleted successfully'
        ]);
    }
}
