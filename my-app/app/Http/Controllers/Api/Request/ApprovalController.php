<?php

namespace App\Http\Controllers\Api\Request;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Approval;
use App\Models\Request as RequestModel;
use App\Models\RequestStatusLog;
use App\Customs\Services\NotificationService;

class ApprovalController extends Controller
{

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

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
        'SUPERVISOR' => 'PENDING_CLUSTER_HEAD',
        'CLUSTER_HEAD' => 'PENDING_INVENTORY',
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


    // 🔔 NOTIFICATIONS
    if ($req->status === 'REJECTED') {

        // rejected always goes back to OPERATION
        $this->notificationService->notifyRoleStatus(
            'OPERATION',
            $req->id,
            $role,
            'REJECTED'
        );

    } else {

        $nextRole = $this->getNextRoleFromStatus($req->status);

        if ($nextRole) {
            $this->notificationService->notifyRoleStatus(
                $nextRole,
                $req->id,
                $role,
                'PENDING'
            );
        }
    }

    $req->save();

    return response()->json(['message' => 'Action completed']);
}

private function getNextRoleFromStatus(string $status): ?string
{
    if (str_starts_with($status, 'PENDING_')) {
        return str_replace('PENDING_', '', $status);
    }

    return match ($status) {
        'SHIPPED'  => 'OPERATION',
        'RECEIVED' => 'INVENTORY',
        default    => null,
    };
}

}
