<?php

namespace App\Http\Controllers\Api\Request;

use App\Http\Controllers\Controller;
use App\Models\RequestStatusLog;
use Illuminate\Http\Request;
use App\Models\Request as RequestModel;
use App\Models\Product;
use App\Models\Approval;
use App\Http\Requests\AddRequest;
use App\Http\Requests\UpdateRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

use App\Customs\Services\NotificationService;

class RequestController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    //
    public function index(Request $request)
    {
        $query = RequestModel::with('items');

        // 🔍 Search filter
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

    public function store(AddRequest $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $validated = $request->validated();

        $req = null;

        DB::transaction(function () use ($validated, $user, &$req) {

            $req = RequestModel::create([
                'requestor_id' => $validated['requestor_id'],
                'status'       => $validated['status'],
            ]);

            foreach ($validated['items'] as $item) {

                $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                $startingBalance = $product->stock;
                $endingBalance   = $startingBalance - $item['quantity'];

                // if ($endingBalance < 0) {
                //     throw new \Exception("Insufficient stock for {$product->product_name}");
                // }

                $product->update([
                    'quantity' => $endingBalance,
                ]);

                $req->items()->create([
                    'product_id'       => $product->id,
                    'unit_id'          => $item['unit_id'],
                    'quantity'         => $item['quantity'],
                    'starting_balance' => $startingBalance,
                    'ending_balance'   => $endingBalance,
                ]);
            }

            // ✅ FIX: use array access
            $remarks = trim($validated['remarks'] ?? '');

            $formattedRemarks = "[OPERATION]: Ordered. " . (
                $remarks !== '' ? $remarks : 'No remarks provided'
            );

            Approval::create([
                'request_id'  => $req->id,
                'approver_id' => $user->id,
                'role_id'     => $user->id,
                'action'      => 'ORDERED',
                'remarks'     => $formattedRemarks
            ]);

            RequestStatusLog::create([
                'request_id' => $req->id,
                'updated_by' => $user->id,
                'status'     => $req->status,
            ]);

            $this->notificationService->notifyRoleStatus(
                'ACCOUNTING',
                $req->id,
                'ACCOUNTING',
                'PENDING'
            );
        });

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

public function pending(Request $request)
{
    $user = auth()->user();
    $roleName = $user->role->role_name;

    $query = RequestModel::query()
        ->with([
            'requestor:id,firstname',

            'items' => function ($q) {
                $q->select('id', 'request_id', 'product_id', 'unit_id', 'quantity')
                  ->with([
                      'unit:id,name',
                      'product:id,category_id,product_name',
                      'product.category:id,name',
                  ]);
            },

            'approvals:id,request_id,remarks,created_at',
            'shipments:id,request_id,shipped_date,received_date,tracking_link',
        ]);


    // 🔍 Search filter
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

    // OPERATION VIEW
    if ($roleName === 'OPERATION') {
        $query->where('requestor_id', $user->id)
              ->whereNotIn('status', ['RECEIVED', 'CANCELLED', 'REJECTED']);

        return $query
            ->orderBy('updated_at', 'desc')
            ->paginate(8);
    }

    // APPROVAL ROLES
    $statusMap = [
        'ACCOUNTING' => 'PENDING_ACCOUNTING',
        'SUPERVISOR' => 'PENDING_SUPERVISOR',
        'CLUSTER_HEAD' => 'PENDING_CLUSTER_HEAD',
        'INVENTORY' => 'PENDING_INVENTORY'
    ];

    abort_unless(isset($statusMap[$roleName]), 403);

    $query->where('status', $statusMap[$roleName]);

    return $query
        ->orderBy('updated_at', 'desc')
        ->paginate(8);
}

public function history(Request $request)
{
    $user = auth()->user();
    $roleName = $user->role->role_name;

    $query = RequestModel::with([
        'requestor:id,firstname',
        'items.unit:id,name',
        'items.product:id,category_id,product_name',
        'items.product.category:id,name',
        'approvals:id,request_id,approver_id,remarks,created_at',
        'shipments:id,request_id,shipped_date,received_date,tracking_link'
    ]);

        // 🔍 Search filter
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

    // ================= OPERATION =================
    if ($roleName === 'OPERATION') {
        return $query
            ->where('requestor_id', $user->id)
            ->whereIn('status', [
                'RECEIVED',
                'CANCELLED',
                'REJECTED'
            ])
            ->latest()
            ->paginate(10);
    }

    // ================= APPROVAL ROLES =================
    return $query
        ->whereHas('approvals', function ($q) use ($user) {
            $q->where('approver_id', $user->id);
        })
        ->latest()
        ->paginate(10);
}


}
