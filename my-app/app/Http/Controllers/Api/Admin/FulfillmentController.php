<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\Request;
use App\Models\Approval;
use App\Models\Request as RequestModel;
use App\Models\RequestStatusLog;
use Illuminate\Support\Facades\DB;
use App\Customs\Services\NotificationService;

class FulfillmentController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /*
    |--------------------------------------------------------------------------
    | SHIP (ADMIN)
    |--------------------------------------------------------------------------
    */
    public function fulfill(Request $request, $id)
    {
        $user = auth()->user();
        $roleName = $user->role->role_name;

        // 🔒 ADMIN ONLY
        abort_unless($roleName === 'ADMINISTRATOR', 403);

        $req = RequestModel::findOrFail($id);

        // ✅ Ensure correct stage
        if ($req->status !== 'PENDING_INVENTORY') {
            return response()->json([
                'message' => 'Request is not ready for shipment'
            ], 422);
        }

        $validated = $request->validate([
            'remarks'=> 'nullable|string',
            'shipments' => ['required', 'array', 'min:1'],
            'shipments.*.shipped_date' => ['required', 'date'],
            'shipments.*.tracking_link' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($request, $req, $user) {

            // ✅ update status
            $req->update([
                'status' => 'SHIPPED',
            ]);

            foreach ($request->shipments as $shipment) {
                Shipment::create([
                    'request_id'    => $req->id,
                    'batch_number' => '1',
                    'shipped_by'   => $req->requestor_id,
                    'shipped_date' => $shipment['shipped_date'],
                    'received_date'=> $shipment['received_date'] ?? null,
                    'tracking_link'=> $shipment['tracking_link'] ?? null,
                    'status'       => $req->status
                ]);
            }

            $formattedRemarks = "[ADMIN]: " . (
                $request->filled('remarks')
                    ? trim($request->remarks)
                    : 'No remarks provided'
            );

            Approval::create([
                'request_id' => $req->id,
                'approver_id' => $user->id,
                'action' => $req->status,
                'remarks' => $formattedRemarks
            ]);

            RequestStatusLog::create([
                'request_id' => $req->id,
                'updated_by' => $user->id,
                'status' => $req->status
            ]);

        });

        // ✅ Notify requestor
        $this->notificationService->notifyUserStatus(
            $req->requestor_id,
            $req->id,
            'ADMIN',
            'SHIPPED'
        );

        return response()->json([
            'message' => 'Order has been shipped by admin',
            'data' => $req->load('shipments')
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | RECEIVE (ADMIN)
    |--------------------------------------------------------------------------
    */
    public function receive(Request $request, $id)
    {
        $user = auth()->user();
        $roleName = $user->role->role_name;

        // 🔒 ADMIN ONLY
        abort_unless($roleName === 'ADMINISTRATOR', 403);

        $req = RequestModel::findOrFail($id);

        // ✅ Ensure correct stage
        if ($req->status !== 'SHIPPED') {
            return response()->json([
                'message' => 'Request is not ready to be received'
            ], 422);
        }

        DB::transaction(function () use ($req, $request, $user) {

            $req->update([
                'status' => 'RECEIVED'
            ]);

            $formattedRemarks = "[ADMIN]: " . (
                $request->filled('remarks')
                    ? trim($request->remarks)
                    : 'No remarks provided'
            );

            Approval::create([
                'request_id' => $req->id,
                'approver_id' => $user->id,
                'action' => 'RECEIVED',
                'remarks' => $formattedRemarks
            ]);

            RequestStatusLog::create([
                'request_id' => $req->id,
                'updated_by' => $user->id,
                'status' => 'RECEIVED'
            ]);

            // ✅ Update shipments
            $req->shipments()->update([
                'status' => 'RECEIVED',
                'received_date' => now()
            ]);
        });

        // ✅ Notify inventory (optional)
        $this->notificationService->notifyRoleStatus(
            'INVENTORY',
            $req->id,
            'OPERATIONS',
            'RECEIVED'
        );

        return response()->json([
            'message' => 'Order has been received by admin'
        ]);
    }
}