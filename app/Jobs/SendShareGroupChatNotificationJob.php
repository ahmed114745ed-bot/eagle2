<?php

namespace App\Jobs;

use App\Helpers\Common;
use App\Http\Resources\Api\V1\GroupChatResource;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendShareGroupChatNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $text;
    protected $groupChatResource;

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
            Log::info('SendShareGroupChatNotificationJob: Starting job', [
                'user_id' => $this->user->id,
                'text' => $this->text,
                'has_image' => !empty($this->groupChatResource['image_url'] ?? '')
            ]);

            if ($this->text && str_starts_with($this->text, 'share_room:')) {
                Log::info('SendShareGroupChatNotificationJob: Detected share_room pattern');
                $this->handleShareRoom();
                return;
            }

            Log::warning('SendShareGroupChatNotificationJob: No share_room pattern found', [
                'text' => $this->text
            ]);
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
            $roomId = $parts[1] ?? null;
            $roomImage = $this->groupChatResource['image_url'] ?? '';

            Log::info('SendShareGroupChatNotificationJob: Processing share room', [
                'room_id' => $roomId,
                'room_image' => $roomImage,
                'parts_count' => count($parts)
            ]);

            if (!$roomId) {
                Log::error('SendShareGroupChatNotificationJob: Room ID not found in text', [
                    'text' => $this->text
                ]);
                return;
            }

            $userLang = $this->user->lan ?? 'en';
            $translatedMessage = __('share_room_message', [], $userLang);

            if (!empty($roomImage)) {
                if (!str_starts_with($roomImage, 'http')) {
                    $roomImage = config('app.url') . '/storage/' . $roomImage;
                }
            }

            Log::info('SendShareGroupChatNotificationJob: Room image URL', [
                'room_image' => $roomImage
            ]);

            $notificationsIdsChunks = User::withoutAppends()->where('notification_id', '!=', null)
                ->select(['id', 'notification_id', 'lan'])
                ->orderByDesc('online')
                ->where('id', '!=', $this->user->id)
                ->limit(5000)
                ->get()
                ->unique('notification_id')
                ->chunk(800);

            $totalSent = 0;
            $totalFailed = 0;

            foreach ($notificationsIdsChunks as $chunkIndex => $notificationsIds) {
                Log::info('SendShareGroupChatNotificationJob: Processing chunk', [
                    'chunk_index' => $chunkIndex,
                    'chunk_size' => count($notificationsIds)
                ]);

                foreach ($notificationsIds as $notificationUser) {
                    try {
                        $userLanguage = $notificationUser->lan ?? 'en';
                        $localizedMessage = __('share_room_message', [], $userLanguage);
                        
                        $title = ($this->user->name ?? '') . ' (' . config('app.name_en') .')';
                        
                        $result = Common::send_firebase_notification_with_room_image(
                            $notificationUser->notification_id,
                            $title,
                            $localizedMessage,
                            $roomImage,
                            $roomId,
                            data: [
                                'title' => $title,
                                'sub-title' => $localizedMessage,
                                'room_id' => $roomId,
                                'room_image' => $roomImage
                            ],
                            messageType: 'share-room',
                            user: $this->user
                        );

                        if ($result) {
                            $totalSent++;
                        } else {
                            $totalFailed++;
                        }
                    } catch (\Throwable $e) {
                        $totalFailed++;
                        Log::error('SendShareGroupChatNotificationJob: Failed to send to user', [
                            'user_id' => $notificationUser->id ?? 'unknown',
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            Log::info('SendShareGroupChatNotificationJob: Job completed', [
                'room_id' => $roomId,
                'total_sent' => $totalSent,
                'total_failed' => $totalFailed
            ]);
        } catch (\Throwable $e) {
            Log::error('SendShareGroupChatNotificationJob: Exception in handleShareRoom', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
