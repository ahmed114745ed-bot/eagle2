<?php

namespace App\Jobs;

use App\Helpers\Common;
use App\Http\Resources\Api\V1\GroupChatResource;
use App\Models\User;
use App\Models\Room;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SendShareGroupChatNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $text;
    protected $groupChatResource;

    // ⏱️ زيادة timeout للـ job لأنه قد يستغرق وقتاً طويلاً عند معالجة آلاف الإشعارات
    public $timeout = 600; // 10 دقائق

    public function __construct(User $user, ?string $text, array $groupChatResource)
    {
        $this->user = $user;
        $this->text = $text;
        $this->groupChatResource = $groupChatResource;
    }


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
    

            if ($this->text && str_starts_with($this->text, 'share_room:')) {
                $this->handleShareRoom();
                return;
            }

        } catch (\Throwable $e) {
            Log::error('SendShareGroupChatNotificationJob: Exception in handle', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Handle share room notification with image
     */
    protected function handleShareRoom(): void
    {
        try {
            $parts = explode(':', $this->text);
            
            $roomId = $parts[3] ?? null;

            if (!$roomId) {
                return;
            }
            $room = Room::select('id', 'room_cover', 'final_room_image')->find($roomId);
            
            if (!$room) {
                return;
            }

            $roomImage = $room->room_cover ?? $room->final_room_image ?? $this->groupChatResource['image_url'] ?? '';
            $userLang = $this->user->lan ?? 'en';
            $translatedMessage = __('share_room_message', [], $userLang);

            if (!empty($roomImage)) {
                $roomImage = getImagePath($roomImage);
            }
            
            // ✅ تحسين الأداء: استخدام cursor بدلاً من get() لتقليل استهلاك الذاكرة
            // ⚠️ تم نقل unique() قبل limit() لتجنب تحميل البيانات في الذاكرة
            // ⚠️ تم استبدال where('!=', null) بـ whereNotNull() للأداء الأفضل
            $notificationsIdsChunks = User::withoutAppends()
                ->whereNotNull('notification_id')
                ->select(['id', 'notification_id', 'lan'])
                ->orderByDesc('online')
                ->where('id', '!=', $this->user->id)
                ->distinct('notification_id') // ✅ استخدام distinct في الـ query بدلاً من unique() بعد cursor()
                ->limit(5000)
                ->cursor() // ✅ استخدام cursor بدلاً من get()
                ->chunk(800);

            $totalSent = 0;
            $totalFailed = 0;

            foreach ($notificationsIdsChunks as $chunkIndex => $notificationsIds) {
                // ✅ معالجة الـ chunk بشكل متوازي أو إرسال دفعات
                $this->sendNotificationBatch($notificationsIds, $roomId, $roomImage, $translatedMessage, $totalSent, $totalFailed);
            }

        
        } catch (\Throwable $e) {
            Log::error('SendShareGroupChatNotificationJob: Exception in handleShareRoom', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * ✅ دالة منفصلة لإرسال دفعة من الإشعارات
     */
    protected function sendNotificationBatch($notificationsIds, $roomId, $roomImage, $translatedMessage, &$totalSent, &$totalFailed): void
    {
        $notificationIds = $notificationsIds->pluck('notification_id')->toArray();
        
        if (empty($notificationIds)) {
            return;
        }

        try {
            $title = ($this->user->name ?? '') . ' (' . config('app.name_en') . ')';
            
            $result = Common::send_firebase_notification_with_room_image(
                $notificationIds,
                $title,
                $translatedMessage,
                $roomImage,
                $roomId,
                data: [
                    'title' => $title,
                    'sub-title' => $translatedMessage,
                    'room_id' => $roomId,
                    'room_image' => $roomImage
                ],
                messageType: 'share-room',
                user: $this->user
            );

            if ($result) {
                $totalSent += count($notificationIds);
            } else {
                $totalFailed += count($notificationIds);
            }
        } catch (\Throwable $e) {
            $totalFailed += count($notificationIds);
            Log::error('SendShareGroupChatNotificationJob: Failed to send batch', [
                'batch_size' => count($notificationIds),
                'error' => $e->getMessage()
            ]);
        }
    }
}
