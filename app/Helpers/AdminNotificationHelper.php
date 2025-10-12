<?php

namespace App\Helpers;

use App\Events\AdminNotificationCreated;
use App\Models\AdminNotification;
use App\Enums\AdminNotificationType;
use Illuminate\Support\Facades\Auth;

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

  

}
