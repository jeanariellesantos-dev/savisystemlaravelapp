<?php

namespace App\Http\Controllers\Api\Request;

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
    //
    public function fulfill(Request $request, $id)
    {
        $user = auth()->user();
        $roleName = $user->role->role_name;
        $role = strtoupper($roleName);

        abort_unless($roleName === 'INVENTORY', 403);

        $req = RequestModel::findOrFail($id);

         // Optional: ensure correct status
        if ($req->status !== 'PENDING_INVENTORY') {
            return response()->json([
                'message' => 'Request is not ready for shipment'
            ], 422);
        }

        // Optional status transition
        $req->update([
            'status' => 'SHIPPED',
        ]);

        $validated = $request->validate([
            'remarks'=> 'nullable|string',
            'shipments' => ['required', 'array', 'min:1'],
            'shipments.*.shipped_date' => ['required', 'date'],
            'shipments.*.tracking_link' => ['nullable', 'string'],
        ]);

        foreach ($request->shipments as $shipment) {
            Shipment::create([
                'request_id'    => $req->id,
                'batch_number'    => '1',
                'shipped_by'    => $req->requestor_id,
                'shipped_date'  => $shipment['shipped_date'],
                'received_date' => $shipment['received_date'] ?? null,
                'tracking_link' => $shipment['tracking_link'],
                'status' => $req->status
            ]);
        }
        // ✅ Prefix remarks with role (only if remarks exist)
        $formattedRemarks = "[{$role}]: " . (
            $request->filled('remarks')
                ? trim($request->remarks)
                : 'No remarks provided'
        );

        Approval::create([
            'request_id' => $req->id,
            'approver_id' => auth()->id(),
            'action' => $req->status,
            'remarks' => $formattedRemarks
        ]);

        RequestStatusLog::create([
            'request_id' => $req->id,
            'updated_by' => auth()->id(),
            'status' => $req->status
         ]);   
         
        $this->notificationService->notifyUserStatus(
                $req->requestor_id,
                $req->id,
                'INVENTORY',
                $req->status
        );

        return response()->json([
            'message' => 'Order has been shipped',
            'data' => $req->load('shipments')
        ], 201);
    }

        public function receive(Request $request, $id)
    {
        $user = auth()->user();
        $roleName = $user->role->role_name;
        $role = strtoupper($roleName);

        abort_unless($roleName === 'OPERATION', 403);

        $req = RequestModel::findOrFail($id);
        $req->status = 'RECEIVED';
        $req->save();

         // ✅ Prefix remarks with role (only if remarks exist)
        $formattedRemarks = "[{$role}]: " . (
            $request->filled('remarks')
                ? trim($request->remarks)
                : 'No remarks provided'
        );

        Approval::create([
            'request_id' => $req->id,
            'approver_id' => auth()->id(),
            'action' => $req->status,
            'remarks' => $formattedRemarks
        ]);

        RequestStatusLog::create([
            'request_id' => $req->id,
            'updated_by' => auth()->id(),
            'status' => $req->status
         ]);

        $this->notificationService->notifyRoleStatus(
            'INVENTORY',
            $req->id,
            'OPERATION',
            'RECEIVED'
        );
         
        DB::transaction(function () use ($req) {

            $req->shipments()->update([
                'status' => 'RECEIVED',
                'received_date' => now()
            ]);
     
            // RequestStatusLog::create([
            //     'request_id' => $req->id,
            //     'updated_by' => auth()->id(),
            //     'status' => $req->status
            // ]);
        });

        return response()->json(['message' => 'Order has been received']);
    }
}
