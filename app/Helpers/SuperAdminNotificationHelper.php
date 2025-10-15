<?php

namespace App\Helpers;

use App\Events\SuperAdminNotificationCreated;
use App\Jobs\SendFirebaseNotificationsToAdmins;
use App\Models\SuperAdmin;
use App\Models\SuperAdminNotification;
use App\Enums\SuperAdminNotificationType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\Admin;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Google_Client;
class SuperAdminNotificationHelper
{
    public static function notify(
        SuperAdminNotificationType $type,
        string $title,
        ?string $message = null,
        $model = null,
        ?array $data = [],
        ?int $superAdminId = null
    ): SuperAdminNotification {
        $notification = SuperAdminNotification::create([
            'type' => $type->value,
            'title' => $title,
            'message' => $message,
            'model_id' => $model?->id,
            'model_type' => $model ? get_class($model) : null,
            'data' => $data ?? [],
            'super_admin_id' => $superAdminId,
        ]);
        $url='';
        $previewUrl = $data['preview_url'] ?? null;
        if ($previewUrl) {
            $enum = \App\Enums\SuperAdminNotificationLink::tryFrom($previewUrl?->value);
            if ($enum) {
                $url = $enum->url($data);
            }
        }
        broadcast(new SuperAdminNotificationCreated($notification))->toOthers();
        if ($superAdminId) {
            $superAdmin = SuperAdmin::find($superAdminId);
            $token = $superAdmin?->fcm_token;
            if (!empty($token)) {
                self::sendNotification($token, $title, $message, $url);
            }
        }
    
        return $notification;

    }

    public static function markAsRead(SuperAdminNotification $notification): void
    {
        $notification->update(['is_read' => true, 'read_at' => now()]);
    }

   
    public static function unreadCount(?int $adminId = null): int
    {
        $adminId = $adminId ?? Auth::id();

        return SuperAdminNotification::
            where('is_read', false)
            ->count();
    }

    public static function sendNotification($token, $title, $body, $url)
    {
        $projectId = env('FIREBASE_PROJECT_NAME');
        $firebaseConfigPath = public_path('firebase_credentials.json');
        $client = new Google_Client();
        $client->setAuthConfig($firebaseConfigPath);

        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->fetchAccessTokenWithAssertion();
        $accessToken = $client->getAccessToken()['access_token'];

        $payload = [
            "message" => [
                "token" => $token,
                "notification" => [
                    "title" => $title,
                    "body" => $body,
                ]
                ,
                "data" => [ 
                    "click_action" => $url
                ]
            ]
        ];
     
        $response = Http::withHeaders([
            "Authorization" => "Bearer $accessToken",
            "Content-Type" => "application/json",
        ])->post("https://fcm.googleapis.com/v1/projects/$projectId/messages:send", $payload);
      
   
        return $response->json();
    }

  

}


