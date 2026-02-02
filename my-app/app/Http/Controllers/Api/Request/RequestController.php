<?php

namespace App\Http\Controllers\Api\Request;

use App\Http\Controllers\Controller;
use App\Models\RequestStatusLog;
use Illuminate\Http\Request;
use App\Models\Request as RequestModel;
use App\Models\Product;
use App\Http\Requests\AddRequest;
use App\Http\Requests\UpdateRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
    //
    public function index(Request $request)
    {
        $query = RequestModel::with('requestItems.product');

        // 🔍 Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('status', 'like', "%{$search}%");
            });
        }

        // 🏷 Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 📄 Pagination
        $perPage = $request->get('per_page', 10);

        return response()->json(
            $query->latest()->paginate($perPage)
        );
    }


    //Add new request
public function store(AddRequest $request)
{
    // ✅ Authenticated via Bearer token (auth:api middleware should already enforce this)
    $user = Auth::user();

    if (!$user) {
        return response()->json(['message' => 'Unauthenticated'], 401);
    }

    // ✅ Validation (you can move this fully into AddRequest later)
    $validated = $request->validate([
        'status' => 'required|string|max:255',
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
    ]);

    DB::transaction(function () use ($validated, $user, &$req) {

        // ✅ Create request
        $req = RequestModel::create([
            'requestor_id' => $user->id,
            'status' => $validated['status'],
        ]);

        // ✅ Create request items + update inventory
        foreach ($validated['items'] as $item) {
            $product = Product::lockForUpdate()->findOrFail($item['product_id']);

            $startingBalance = $product->quantity;
            $endingBalance   = $startingBalance - $item['quantity'];

            if ($endingBalance < 0) {
                throw new \Exception("Insufficient stock for {$product->product_name}");
            }

            $product->update([
                'quantity' => $endingBalance,
            ]);

            $req->items()->create([
                'product_id'       => $product->id,
                'quantity'         => $item['quantity'],
                'starting_balance' => $startingBalance,
                'ending_balance'   => $endingBalance,
            ]);
        }

        // ✅ Log initial status
        RequestStatusLog::create([
            'request_id' => $req->id,
            'updated_by' => $user->id,
            'status'     => $req->status,
        ]);
    });

    // ✅ Return the CREATED request (NOT the HTTP request object)
    return response()->json([
        'message' => 'Request created successfully',
        'data' => $req->load([
            'items.product',
            'requestor',
        ]),
    ], 201);
}



    public function show($id)
    {
        $request = RequestModel::with([
            'approvals' => function ($q) {
                $q->latest();
            },
            'requestItems.product'
        ])->findOrFail($id);

        $latestApproval = $request->approvals->first();

        return response()->json([
            'id' => $request->id,
            'request_id' => $request->request_id,
            'requestor_id' => $request->requestor_id,
            'status' => $request->status,
            'date' => Carbon::parse($request->created_at)->format('Y-m-d, H:i'),
            'approval_remarks' => $latestApproval->remarks ?? '',
            'items' => $request->requestItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'quantity' => $item->quantity,
                    'starting_balance' => $item->starting_balance,
                    'ending_balance' => $item->ending_balance,
                    'product' => [
                        'id' => $item->product->id,
                        'product_name' => $item->product->product_name,
                        'unit_of_measure' => $item->product->unit_of_measure,
                    ]
                ];
            })
        ]);
    }

    public function update(UpdateRequest $request, $id)
    {
        $requestData = RequestModel::findOrFail($id);
        $requestData->update($request->validated());

        // Reload relations
        $requestData->load('requestItems.product');

        return response()->json([
            'message' => 'Request updated successfully',
            'data' => $requestData,
        ]);
    }


    public function destroy($id)
    {
        RequestModel::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Request deleted successfully',
        ]);
    }

public function pending()
{
    $user = auth()->user();

    if ($user->role === 'OPERATION') {
        return RequestModel::where('requestor_id', $user->id)
            ->with([
                'requestor:id,firstname',
                'items.product',
                'approvals:id,remarks'
            ])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    $statusMap = [
        'ACCOUNTING' => 'PENDING_ACCOUNTING',
        'SUPERVISOR' => 'PENDING_SUPERVISOR',
        'INVENTORY' => 'PENDING_INVENTORY'
    ];

    abort_unless(isset($statusMap[$user->role]), 403);

    return RequestModel::where('status', $statusMap[$user->role])
        ->with([
            'requestor:id,firstname',
            'items.product',
            'approvals:id,remarks'
        ])
        ->orderBy('created_at', 'asc')
        ->get();
}




}
