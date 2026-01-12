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

        if ($this->text && str_starts_with($this->text, 'share_room:')) {
            $this->handleShareRoom();
            return;
        }

    }

    /**
     * Handle share room notification with image
     */
    protected function handleShareRoom(): void
    {
        $parts = explode(':', $this->text);
        $roomId = $parts[1] ?? null;
        $roomImage = $this->groupChatResource['image_url'] ?? '';

        $userLang = $this->user->lan ?? 'en';
        $translatedMessage = __('share_room_message', [], $userLang);

        if (!empty($roomImage)) {
            if (!str_starts_with($roomImage, 'http')) {
                $roomImage = config('app.url') . '/storage/' . $roomImage;
            }
        }

        $notificationsIdsChunks = User::withoutAppends()->where('notification_id', '!=', null)
            ->select(['id', 'notification_id', 'lan'])
            ->orderByDesc('online')
            ->where('id', '!=', $this->user->id)
            ->limit(5000)
            ->get()
            ->unique('notification_id')
            ->chunk(800);

        foreach ($notificationsIdsChunks as $notificationsIds) {
            foreach ($notificationsIds as $notificationUser) {
                // Get message in user's language
                $userLanguage = $notificationUser->lan ?? 'en';
                $localizedMessage = __('share_room_message', [], $userLanguage);
                
                $title = ($this->user->name ?? '') . ' (' . config('app.name_en') .')';
                
                Common::send_firebase_notification_with_room_image(
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
            }
        }
    }
}
