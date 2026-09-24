<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends BaseController
{
    public function index(): JsonResponse
    {
        $notifications = $this->managedStore()->notifications()
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $unreadCount = $this->managedStore()->notifications()->unread()->count();

        return $this->successReturn([
            'notifications' => NotificationResource::collection($notifications),
            'unread_count' => $unreadCount,
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    public function read(Request $request, int $notification): JsonResponse
    {
        $this->managedStore()->notifications()->findOrFail($notification)->update(['is_read' => true]);

        return $this->successReturn(message: 'messages.notification_read');
    }

    public function readAll(): JsonResponse
    {
        $this->managedStore()->notifications()->unread()->update(['is_read' => true]);

        return $this->successReturn(message: 'messages.notifications_read_all');
    }

    public function destroy(int $notification): JsonResponse
    {
        $this->managedStore()->notifications()->findOrFail($notification)->delete();

        return $this->successReturn(message: 'messages.notification_deleted');
    }
}
