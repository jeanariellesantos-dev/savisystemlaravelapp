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
    $user = auth()->user();
    $roleName = $user->role->role_name;
    $role = strtoupper( $roleName);

    $flow = [
        'ACCOUNTING' => 'PENDING_SUPERVISOR',
        'SUPERVISOR' => 'PENDING_INVENTORY',
        'INVENTORY' => 'SHIPPED',
        'OPERATION' => 'RECEIVED',
    ];

    if ($request->action === 'REJECTED') {
        $req->status = 'REJECTED';
    } else {
        abort_unless(isset($flow[$role]), 403);
        $req->status = $flow[$role];
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

    return response()->json(['message' => 'Action completed']);
}
}
