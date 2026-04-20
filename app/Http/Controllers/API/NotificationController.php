<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PushNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $notifications = PushNotification::where(function($query) use ($user) {
                if ($user) {
                    $query->where('user_id', $user->id)
                          ->orWhereNull('user_id');
                } else {
                    $query->whereNull('user_id');
                }
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'data'        => $notifications->items(),
            'unread'      => $user ? $user->pushNotifications()->where('is_read', false)->count() : 0,
            'current_page' => $notifications->currentPage(),
            'last_page'    => $notifications->lastPage(),
        ]);
    }

    public function markRead(Request $request, int $id): JsonResponse
    {
        $notification = PushNotification::where('user_id', $request->user()->id)->find($id);

        if (!$notification) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $notification->update(['is_read' => true]);

        return response()->json(['message' => 'Marked as read.']);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->pushNotifications()->update(['is_read' => true]);

        return response()->json(['message' => 'All notifications marked as read.']);
    }
}
