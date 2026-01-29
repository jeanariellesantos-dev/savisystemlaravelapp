<?php

namespace App\Http\Controllers\Api\Request;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Approval;
use App\Models\Request as RequestModel;
use Illuminate\Support\Facades\DB;

class FulfillmentController extends Controller
{
    public function fulfill(Request $request, $id)
    {
        abort_unless(auth()->user()->role === 'INVENTORY', 403);

        $req = RequestModel::findOrFail($id);
        $req->status = 'SHIPPED';
        $req->save();

        $validated = $request->validate([
            'shipment' => 'required|array|min:1',

            'shipment.*.batch_number' => 'required|string',
            'shipment.*.shipped_by' => 'required|exists:users,id',
            'shipment.*.shipped_date' => 'nullable|date',
            'shipment.*.received_date' => 'nullable|date',
            'shipment.*.status' => 'required|string',
        ]);

        DB::transaction(function () use ($validated, $req) {

            $req->shipments()->createMany(
                $validated['shipment']
            );

        });

        Approval::create([
            'request_id' => $req->id,
            'approver_id' => auth()->id(),
            'action' => 'APPROVED',
            'remarks' => $request->remarks
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

        // Approval::create([
        //     'request_id' => $req->id,
        //     'user_id' => auth()->id(),
        //     'action' => 'RECEIVED',
        //     'remarks' => $request->remarks
        // ]);

        return response()->json(['message' => 'Order has been received']);
    }
}
