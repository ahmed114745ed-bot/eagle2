<?php

namespace App\Helpers;

use App\Events\AdminNotificationCreated;
use App\Models\AdminNotification;
use App\Enums\AdminNotificationType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\Admin;

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

        broadcast(new AdminNotificationCreated($notification))->toOthers();
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

    protected static function sendFirebaseNotification(string $title, string $body, ?array $data = [])
    {
        $serverKey = config('services.firebase.server_key', 'AAAA1oVZzQo:APA91bE.....'); // ضع مفتاحك هنا أو في .env

        $admins = Admin::whereNotNull('fcm_token')->pluck('fcm_token')->toArray();
        if (empty($admins)) {
            return;
        }

        $payload = [
            'registration_ids' => $admins,
            'notification' => [
                'title' => $title,
                'body'  => $body,
                'sound' => 'default',
                'icon'  => '/logo.png',
            ],
            'data' => $data ?? [],
        ];

        Http::withHeaders([
            'Authorization' => 'key=' . $serverKey,
            'Content-Type'  => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', $payload);
    }


  

}
