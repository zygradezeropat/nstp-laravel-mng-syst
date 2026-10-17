<?php

namespace App\Http\Controllers;

use App\Models\PortalNotification;
use App\Models\PortalUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * GET /api/notifications?email=instructor@dnsc.edu.ph
     * Returns notifications for the given user email.
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $email = $request->query('email');
        if ($email) {
            $user = PortalUser::where('email', $email)->first() ?? $user;
        }

        if (!$user) {
            return response()->json([]);
        }

        $notifications = PortalNotification::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn(PortalNotification $n) => [
                'id'         => $n->id,
                'type'       => $n->type,
                'title'      => $n->title,
                'message'    => $n->message,
                'is_read'    => (bool)$n->is_read,
                'created_at' => $n->created_at?->diffForHumans() ?? '',
            ]);

        return response()->json($notifications);
    }

    /**
     * POST /api/notifications/{id}/read
     * Marks a notification as read.
     */
    public function markRead(int $id): JsonResponse
    {
        $notification = PortalNotification::findOrFail($id);
        $notification->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * POST /api/notifications/read-all?email=...
     * Marks all notifications as read for a user.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $user = auth()->user();
        $email = $request->query('email');
        if ($email) {
            $user = PortalUser::where('email', $email)->first() ?? $user;
        }

        if (!$user) {
            return response()->json(['success' => false], 404);
        }

        PortalNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }
}
