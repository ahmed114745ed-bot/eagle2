<?php

namespace App\Jobs;

use App\Helpers\Common;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class SendNotificationToAllFollowers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private int $userId)
    {
        //
    }

    public function handle(): void
    {
        $appNameEn = Cache::rememberForever('app_title_en', fn () => Setting::where('key', 'app_title_en')->value('value') ?? 'Default');
        $appNameAr = Cache::rememberForever('app_title_ar', fn () => Setting::where('key', 'app_title_ar')->value('value') ?? 'Default');

        // Load user and profile in one query
        $owner = User::query()
            ->with(['profile:id,user_id,avatar'])
            ->select('id', 'name')
            ->findOrFail($this->userId); // safer than find()

        // Get followers via relationship
        $followers = $owner->followerss()
            ->select('id', 'notification_id', 'lan')
            ->get();

        $usersTokenEn = $followers->where('lan', '!=', 'ar')->pluck('notification_id')->filter()->values();
        $usersTokenAr = $followers->where('lan', 'ar')->pluck('notification_id')->filter()->values();

        // Notification content
        $bodyAr = __('api.enter_room', ['name' => $owner->name], 'ar');
        $bodyEn = __('api.enter_room', ['name' => $owner->name], 'en');
        $icon = $owner->profile->avatar;

        $data = [
            'image' => getDriverUrl().'/'.$icon,
            'owner_id' => $owner->id,
            'name' => $owner->name,
        ];

        // Send notifications in chunks
        $usersTokenEn->chunk(100)->each(fn ($chunk) => Common::send_firebase_notification(
            $chunk->all(),
            $appNameEn,
            $bodyEn,
            $icon,
            $data,
            messageType: 'enter-room'
        )
        );

        $usersTokenAr->chunk(100)->each(fn ($chunk) => Common::send_firebase_notification(
            $chunk->all(),
            $appNameAr,
            $bodyAr,
            $icon,
            $data,
            messageType: 'enter-room'
        )
        );
    }
}
