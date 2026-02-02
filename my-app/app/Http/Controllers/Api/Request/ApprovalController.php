<?php

namespace App\Http\Controllers\Api\Request;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Approval;
use App\Models\Request as RequestModel;
use App\Models\RequestStatusLog;

class ApprovalController extends Controller
{
    public function approve(Request $request, $id)
{
    $request->validate([
        'action' => 'required|in:APPROVED,REJECTED',
        'remarks' => 'nullable|string'
    ]);

    $req = RequestModel::findOrFail($id);
    $role = auth()->user()->role;

    $flow = [
        'ACCOUNTING' => 'PENDING_SUPERVISOR',
        'SUPERVISOR' => 'PENDING_INVENTORY',
    ];

    if ($request->action === 'REJECTED') {
        $req->status = 'REJECTED';
    } else {
        abort_unless(isset($flow[$role]), 403);
        $req->status = $flow[$role];
    }

    Approval::create([
        'request_id' => $req->id,
        'approver_id' => auth()->id(),
        'action' => $request->action,
        'remarks' => $request->remarks
    ]);

    RequestStatusLog::create([
        'request_id' => $req->id,
        'updated_by' => auth()->id(),
        'status' => $req->status
    ]);

    $req->save();

    return response()->json(['message' => 'Action completed']);
}
}
