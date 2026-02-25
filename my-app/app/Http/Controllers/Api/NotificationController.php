<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;

class NotificationController extends Controller
{
    // ================= GET ALL =================
public function index()
{
    $notifications = Notification::with('request')
        ->where('user_id', auth()->id())
        ->latest()
        ->take(10)
        ->get();

    return response()->json(
        $notifications->map(function ($n) {
            return [
                'id' => $n->id,
                'title' => $n->title ?? 'Notification',
                'message' => $n->message ?? '',
                'is_read' => $n->is_read,
                'created_at' => $n->created_at,

                // 👇 actual request data instead of request_id
                'request' => $n->request ? [
                    'id' => $n->request->id,
                    'request_id' => $n->request->request_id,
                    'status' => $n->request->status,
                    'created_at' => $n->request->created_at,
                ] : null
            ];
        })
    );
}

    // ================= UNREAD COUNT =================
    public function unreadCount()
    {
        $count = Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'unread' => $count
        ]);
    }

    // ================= MARK ONE AS READ =================
    public function markAsRead($id)
    {
        $notif = Notification::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if (!$notif->read_at) {
            $notif->update([
                'is_read' => 1
            ]);
        }

        return response()->json([
            'message' => 'Notification marked as read'
        ]);
    }

    // ================= MARK ALL AS READ =================
    public function markAllAsRead()
    {
        Notification::where('user_id', auth()->id())
            ->update([
                'is_read' => 1,
            ]);

        return response()->json([
            'message' => 'All notifications marked as read'
        ]);
    }
}