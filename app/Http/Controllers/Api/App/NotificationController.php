<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Http\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ResponseTrait;

    public function index(Request $request): JsonResponse
    {
        $request->user()->notifications()->unread()->update(['is_read' => true]);

        $notifications = $request->user()->notifications()
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return $this->successReturn([
            'notifications' => NotificationResource::collection($notifications),
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
        $request->user()->notifications()->findOrFail($notification)->update(['is_read' => true]);

        return $this->successReturn(message: 'messages.notification_read');
    }

    public function readAll(Request $request): JsonResponse
    {
        $request->user()->notifications()->unread()->update(['is_read' => true]);

        return $this->successReturn(message: 'messages.notifications_read_all');
    }
}
