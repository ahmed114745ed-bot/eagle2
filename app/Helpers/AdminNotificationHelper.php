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
        self::sendFirebaseNotification($title, $message, $data);

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

    protected static function sendFirebaseNotification(string $title, ?string $body = null, ?array $data = [])
    {
        try {
            $factory = (new Factory)->withServiceAccount(config('services.firebase.credentials'));
            $messaging = $factory->createMessaging();

            $tokens = Admin::whereNotNull('fcm_token')->pluck('fcm_token')->toArray();
            if (empty($tokens)) return;

            $message = CloudMessage::new()
                ->withNotification([
                    'title' => $title,
                    'body'  => $body ?? '',
                ])
                ->withData($data ?? []);

            $messaging->sendMulticast($message, $tokens);
        } catch (\Throwable $e) {
            \Log::error('Firebase send error: ' . $e->getMessage());
        }
    }

  

}
