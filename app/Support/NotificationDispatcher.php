<?php

namespace App\Support;

use App\Models\User;
use App\Models\UserNotification;
use App\Notifications\DashboardNotification;
use Illuminate\Database\Eloquent\Collection;

/**
 * Writes notifications to the `user_notifications` table and mirrors them to
 * the device over FCM when the driver is installed.
 *
 * Persisting the row is what makes a notification visible in the app, so it is
 * never skipped. Push is strictly best effort: a missing driver, a missing
 * token or a Firebase outage must never fail the send.
 */
class NotificationDispatcher
{
    /**
     * @var list<string>
     */
    public const AUDIENCES = ['clients', 'vendors', 'all'];

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function sendTo(User $recipient, array $attributes): UserNotification
    {
        $notification = $recipient->notifications()->create([
            ...$attributes,
            'store_id' => $attributes['store_id'] ?? $this->storeIdFor($recipient),
        ]);

        $this->push($recipient, $notification);

        return $notification;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function broadcast(string $audience, array $attributes): int
    {
        $recipients = $this->recipients($audience);

        foreach ($recipients as $recipient) {
            $this->sendTo($recipient, $attributes);
        }

        return $recipients->count();
    }

    /**
     * @return Collection<int, User>
     */
    public function recipients(string $audience): Collection
    {
        return match ($audience) {
            'vendors' => User::vendor()->get(),
            'clients' => User::customer()->get(),
            default => User::whereIn('role', ['customer', 'vendor'])->get(),
        };
    }

    /**
     * Vendors read their feed through their managed store, so a vendor row is
     * given both a user and a store to be visible in either app.
     */
    private function storeIdFor(User $recipient): ?int
    {
        if (! $recipient->isVendor()) {
            return null;
        }

        return $recipient->stores()->orderBy('id')->value('id');
    }

    private function push(User $recipient, UserNotification $notification): void
    {
        if (! DashboardNotification::fcmAvailable()) {
            return;
        }

        $body = $notification->message_en ?: $notification->message_ar;

        if ($body === null) {
            return;
        }

        $recipient->notify(new DashboardNotification(
            title: $notification->title_en ?: $notification->title_ar,
            text: $body,
        ));
    }
}
