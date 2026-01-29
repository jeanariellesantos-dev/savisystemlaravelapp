<?php

namespace App\Http\Controllers\Api\Request;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Request as RequestModel;
use App\Models\Product;
use App\Http\Requests\AddRequest;
use App\Http\Requests\UpdateRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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
        $validated = $request->validate([
            'status' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        Log::info($request);

        DB::transaction(function () use ($validated, &$request) {
            $req = RequestModel::create([
                'requestor_id' => auth()->id(),
                'status' => $request->status,
            ]);

            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);

                $startingBalance = $product->quantity;
                $endingBalance = $startingBalance - $item['quantity'];

                // Update product stock
                $product->update([
                    'quantity' => $endingBalance
                ]);

                $req->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'starting_balance' => $startingBalance,
                    'ending_balance' => $endingBalance
                ]);
            }
        });

        // $req = RequestModel::create($validated);

       return response()->json([
            'message' => 'Request created successfully',
            'data' => $request->load('items.product')
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

        // REQUESTOR: return only his own requests
        if ($user->role === 'OPERATION') {
            return RequestModel::where('requestor_id', $user->id)
                ->with([
                    'requestor:id,firstname',
                    'requestItems.product'
                ])
                ->orderBy('created_at', 'desc')
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
                'requestItems.product'
            ])
            ->orderBy('created_at', 'asc')
            ->get();
    }



}
