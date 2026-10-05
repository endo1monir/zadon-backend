<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NotificationRequest;
use App\Models\User;
use App\Models\UserNotification;
use App\Support\AdminOptions;
use App\Support\NotificationDispatcher;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationDispatcher $dispatcher) {}

    public function index(Request $request): View
    {
        $notifications = UserNotification::query()
            ->with('user:id,name,phone,role')
            ->when($request->filled('search'), fn ($query) => $query->where(function ($query) use ($request): void {
                $term = '%'.$request->string('search')->trim().'%';

                $query->where('title_ar', 'like', $term)
                    ->orWhere('title_en', 'like', $term)
                    ->orWhereHas('user', fn ($query) => $query->where('name', 'like', $term));
            }))
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')->toString()))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.notifications.index', [
            'notifications' => $notifications,
            'filters' => $request->only(['search', 'type']),
            'typeOptions' => AdminOptions::notificationTypes(),
        ]);
    }

    public function store(NotificationRequest $request): RedirectResponse
    {
        if (! $request->filled('audience')) {
            throw ValidationException::withMessages([
                'audience' => __('admin.validation.notification_audience_required'),
            ]);
        }

        $count = $this->dispatcher->broadcast(
            $request->string('audience')->toString(),
            $this->payload($request),
        );

        return back()->with('success', __('admin.flash.notification_broadcast_sent', ['count' => $count]));
    }

    public function sendToUser(NotificationRequest $request, User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            return back()->with('error', __('admin.flash.notification_admins_skipped'));
        }

        $this->dispatcher->sendTo($user, $this->payload($request));

        return back()->with('success', __('admin.flash.notification_sent', ['name' => $user->name]));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(NotificationRequest $request): array
    {
        return $request->safe()->only([
            'title_ar',
            'title_en',
            'message_ar',
            'message_en',
            'type',
            'action_label_ar',
            'action_label_en',
        ]);
    }
}
