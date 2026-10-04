<?php

namespace App\Notifications;

use App\Notifications\Channels\FailSafeFcmChannel;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

/**
 * Push payload for a notification that has already been persisted to the
 * `user_notifications` table. The database row is the source of truth; this
 * only mirrors the message to the device, and stays silent when the FCM
 * driver is not installed.
 */
class DashboardNotification extends Notification
{
    public function __construct(
        public string $title,
        public string $text,
    ) {}

    /**
     * Whether the FCM driver can actually be loaded and used.
     */
    public static function fcmAvailable(): bool
    {
        return class_exists(FcmChannel::class) && class_exists(FcmMessage::class);
    }

    /**
     * @return list<class-string>
     */
    public function via(object $notifiable): array
    {
        if (! self::fcmAvailable()) {
            return [];
        }

        return [FailSafeFcmChannel::class];
    }

    public function toFcm(object $notifiable): ?FcmMessage
    {
        if (! self::fcmAvailable()) {
            return null;
        }

        return new FcmMessage(
            notification: new FcmNotification(
                title: $this->title,
                body: $this->text,
            )
        );
    }
}
