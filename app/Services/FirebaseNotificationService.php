<?php

namespace App\Services;

use Kreait\Firebase\Factory;

class FirebaseNotificationService
{
    protected $messaging;

    public function __construct()
    {
        $credentialsPath = config('services.firebase.credentials');
        if (!$credentialsPath || !file_exists($credentialsPath)) {
            $this->messaging = null;
            return;
        }
        $firebase = (new Factory)
            ->withServiceAccount($credentialsPath);

        $this->messaging = $firebase->createMessaging();
    }

    public function sendNotification($deviceToken, $title, $body)
    {
        if (!$this->messaging) {
            return null;
        }
        $message = [
            'token' => $deviceToken,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
        ];

        return $this->messaging->send($message);
    }
}
