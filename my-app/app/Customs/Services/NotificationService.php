<?php

namespace App\Customs\Services;

use App\Models\User;
use App\Models\Notification;

class NotificationService
{
    public function notifyRoleStatus(
    string $targetRole,
    int $requestId,
    string $actionRole,
    string $action
    ): void {

        $actor = $this->roleText($actionRole);
        $target = $this->roleText($targetRole);

        $title = match ($action) {
            'PENDING'  => "Pending {$target} Approval",
            'APPROVED' => "Approved by {$actor}",
            'REJECTED' => "Rejected by {$actor}",
            'SHIPPED'  => "Shipped by {$actor}",
            'RECEIVED' => "Received by {$actor}",
            default    => "Request Update"
        };

        $message = match ($action) {
            'PENDING'  => "Request is now pending {$target} approval.",
            'APPROVED' => "Request has been approved by {$actor}.",
            'REJECTED' => "Request has been rejected by {$actor}.",
            'SHIPPED'  => "Request has been shipped by {$actor}.",
            'RECEIVED' => "Request has been received by {$actor}.",
            default    => "Request status has been updated."
        };

        $this->notifyRole(
            $targetRole,
            $requestId,
            $title,
            $message
        );
    }

    public function notifyUserStatus(
        int $userId,
        int $requestId,
        string $actionRole,
        string $action
    ): void {

        $actor = $this->roleText($actionRole);

        $title = match ($action) {
            'APPROVED'  => "Approved by {$actor}",
            'REJECTED'  => "Rejected by {$actor}",
            'CANCELLED' => "Cancelled by {$actor}",
            'ON_HOLD'   => "Request put on hold",
            'SHIPPED'   => "Shipped by {$actor}",
            'RECEIVED'  => "Received by {$actor}",
            default     => "Request Update"
        };

        $message = match ($action) {
            'APPROVED'  => "Your request has been approved by {$actor}.",
            'REJECTED'  => "Your request has been rejected by {$actor}.",
            'CANCELLED' => "Your request has been cancelled by {$actor}.",
            'ON_HOLD'   => "Your request is currently on hold.",
            'SHIPPED'   => "Your request has been shipped by {$actor}.",
            'RECEIVED'  => "Your request has been received.",
            default     => "Your request status has been updated."
        };

        Notification::create([
            'user_id'    => $userId,
            'request_id' => $requestId,
            'title'      => $title,
            'message'    => $message,
        ]);
    }

    public function notifyRole(
        string $roleName,
        int $requestId,
        string $title,
        string $message
    ): void {

        $users = User::whereHas('role', function ($q) use ($roleName) {
            $q->where('role_name', $roleName);
        })->get();

        foreach ($users as $user) {

            $notif = Notification::create([
                'user_id'    => $user->id,
                'request_id' => $requestId,
                'title'      => $title,
                'message'    => $message,
            ]);

        }
    }
    private function roleText(string $role): string
{
    return match ($role) {
        'ACCOUNTING'    => 'Accounting',
        'SUPERVISOR'    => 'Supervisor',
        'CLUSTER_HEAD'  => 'Cluster Head',
        'INVENTORY'     => 'Inventory',
        'OPERATION'     => 'Operations',
        default         => ucfirst(strtolower($role)),
    };
}
}