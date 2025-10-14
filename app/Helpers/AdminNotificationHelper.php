<?php

namespace App\Helpers;

use App\Events\AdminNotificationCreated;
use App\Models\AdminNotification;
use App\Enums\AdminNotificationType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\Admin;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Google_Client;
class AdminNotificationHelper
{
    public static function notify(
        AdminNotificationType $type,
        string $title,
        ?string $message = null,
        $model = null,
        ?array $data = [],
        ?int $adminId = null
    ): AdminNotification {
        $notification = AdminNotification::create([
            'type' => $type->value,
            'title' => $title,
            'message' => $message,
            'model_id' => $model?->id,
            'model_type' => $model ? get_class($model) : null,
            'data' => $data ?? [],
            'admin_id' => $adminId,
        ]);
        $url='';
        $admins = Admin::where('id',1)->get();
        $tokens = $admins->pluck('fcm_token')->filter()->all(); 
        broadcast(new AdminNotificationCreated($notification))->toOthers();
        foreach ($tokens as $token) {
            self::sendNotification($token ,$title, $message, $url);
        }

        return $notification;

    }

    public static function markAsRead(AdminNotification $notification): void
    {
        $notification->update(['is_read' => true, 'read_at' => now()]);
    }

   
    public static function unreadCount(?int $adminId = null): int
    {
        $adminId = $adminId ?? Auth::id();

        return AdminNotification::
            where('is_read', false)
            ->count();
    }

    public static function sendNotification($token, $title, $body, $url)
    {
        // $projectId = env('FIREBASE_PROJECT_ID'); 
        $projectId = env('FIREBASE_PROJECT_NAME'); 

        $client = new Google_Client();
        $firebaseConfigPath = public_path('firebase_credentials.json');
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
      
    dd($response->json());
        return $response->json();
    }

  

}
