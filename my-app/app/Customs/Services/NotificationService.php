<?php

namespace App\Customs\Services;

use App\Models\User;
use App\Models\Notification;
use App\Events\RequestNotificationEvent;

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

            broadcast(
                new RequestNotificationEvent($notif)
            )->toOthers();
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