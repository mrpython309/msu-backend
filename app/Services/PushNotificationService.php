<?php

namespace App\Services;

use Kreait\Laravel\Firebase\Facades\Firebase;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    public function send($tokens, $title, $body, $data = [])
    {
        $messaging = Firebase::messaging();

        Log::info('Inside PushNotificationService');

        foreach ($tokens as $token) {

            Log::info("Sending to token: " . $token);
            Log::info("Message: " . $body);

            $message = CloudMessage::fromArray([
                'token' => $token,

                //  Notification (required for app closed state)
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                ],

                //  Data payload (for Flutter handling)
                'data' => array_merge($data, [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ]),

                // Android specific config
                'android' => [
                    'priority' => 'high',
                    'notification' => [
                        'sound' => 'default',
                        'channel_id' => 'high_importance_channel',
                    ],
                ],

                // iOS (APNs) config
                'apns' => [
                    'headers' => [
                        'apns-priority' => '10',
                    ],
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                        ],
                    ],
                ],
            ]);

            try {
                $messaging->send($message);
                Log::info("Notification sent successfully");
            } catch (\Exception $e) {
                Log::error("FCM Send Error: " . $e->getMessage());
            }
        }
    }
}
