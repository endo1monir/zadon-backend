<?php

namespace App\Notifications\Channels;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\MulticastSendReport;
use NotificationChannels\Fcm\FcmChannel;
use Throwable;

class FailSafeFcmChannel extends FcmChannel
{
    public function __construct(protected Dispatcher $events)
    {
        //
    }

    public function send(mixed $notifiable, Notification $notification): ?Collection
    {
        try {

            Log::info('FailSafe FCM started');

            // Get FCM tokens
            $tokens = Arr::wrap(
                $notifiable->routeNotificationFor('fcm', $notification)
            );

            Log::info('FCM Tokens', [
                'tokens' => $tokens,
            ]);

            if (empty($tokens)) {

                Log::warning('No FCM tokens found');

                return null;
            }

            // Create Firebase message
            $message = $notification->toFcm($notifiable);

            Log::info('FCM Message Created', [
                'class' => get_class($message),
            ]);

            $client = app(Messaging::class);

            $result = Collection::make($tokens)
                ->chunk(self::TOKENS_PER_REQUEST)
                ->map(function ($tokens) use ($client, $message) {

                    Log::info('Sending FCM Multicast', [
                        'count' => count($tokens),
                    ]);

                    return $client->sendMulticast(
                        $message,
                        $tokens->all()
                    );

                })
                ->map(function (MulticastSendReport $report) use ($notifiable, $notification) {

                    Log::info('FCM Failures Details');

                    foreach ($report->failures()->getItems() as $failure) {
                        Log::error('FCM Failure', [
                            'token' => $failure->target()->value(),
                            'error' => $failure->error()->getMessage(),
                        ]);
                    }

                    return $this->checkReportForFailures(
                        $notifiable,
                        $notification,
                        $report
                    );

                });

            Log::info('FCM Finished Successfully');

            return $result;

        } catch (Throwable $e) {

            Log::error('FCM ERROR', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return null;
        }
    }
}
