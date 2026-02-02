<?php

namespace App\Http\Controllers\Api\Request;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\Request;
use App\Models\Approval;
use App\Models\Request as RequestModel;
use App\Models\RequestStatusLog;
use Illuminate\Support\Facades\DB;

class FulfillmentController extends Controller
{
    public function fulfill(Request $request, $id)
    {
        abort_unless(auth()->user()->role === 'INVENTORY', 403);

        $req = RequestModel::findOrFail($id);

        $validated = $request->validate([
            'remarks'=> 'nullable|string',
            'shipments' => ['required', 'array', 'min:1'],
            'shipments.*.shipped_date' => ['required', 'date'],
            'shipments.*.tracking_link' => ['required', 'url'],
        ]);

        // Optional: ensure correct status
        if ($req->status !== 'PENDING_INVENTORY') {
            return response()->json([
                'message' => 'Request is not ready for shipment'
            ], 422);
        }

        foreach ($request->shipments as $shipment) {
            Shipment::create([
                'request_id'    => $req->id,
                'batch_number'    => '1',
                'shipped_by'    => $req->requestor_id,
                'shipped_date'  => $shipment['shipped_date'],
                'received_date' => $shipment['received_date'] ?? null,
                'tracking_link' => $shipment['tracking_link'],
                'status' => 'SHIPPED'
            ]);
        }

        // Optional status transition
        $req->update([
            'status' => 'SHIPPED',
        ]);

        // Approval::create([
        //     'request_id' => $req->id,
        //     'approver_id' => auth()->id(),
        //     'action' => 'APPROVED',
        //     'remarks' => $request->remarks
        // ]);

        RequestStatusLog::create([
            'request_id' => $req->id,
            'updated_by' => auth()->id(),
            'status' => $req->status
         ]);    

        return response()->json([
            'message' => 'Order has been shipped',
            'data' => $req->load('shipments')
        ], 201);
    }

        public function received(Request $request, $id)
    {
        abort_unless(auth()->user()->role === 'OPERATION', 403);

        $req = RequestModel::findOrFail($id);
        $req->status = 'RECEIVED';
        $req->save();

        RequestStatusLog::create([
            'request_id' => $req->id,
            'updated_by' => auth()->id(),
            'status' => $req->status
         ]);   
         
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
