<?php

namespace App\Jobs;

use App\Helpers\Common;
use App\Models\Follow;
use App\Models\Room;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $followers = Follow::where('followed_user_id',$this->userId)->with(['follower' => fn($q) => $q->withoutAppends()])->get();

        $users = $followers->pluck('follower');
        $usersTokenEn = $users->where('lan', '!=' ,'ar')->pluck('notification_id');
        $usersTokenAr = $users->where('lan' ,'ar')->pluck('notification_id');
        $OwnerRoom = User::find($this->userId);

        $body_ar =__('api.enter_room', ['name' => $OwnerRoom->name], 'ar');
        $body_en =__('api.enter_room', ['name' => $OwnerRoom->name], 'en');
        $icon = $OwnerRoom->profile->avatar;
        $data['image'] = getDriverUrl(). '/'. $OwnerRoom->profile->avatar;
        $data['owner_id'] = $this->userId;
        $data['name'] = $OwnerRoom->name;
        Common::send_firebase_notification($usersTokenEn,config('app.name_en'), $body_en,$icon,$data, messageType: 'enter-room', );
        Common::send_firebase_notification( $usersTokenAr,config('app.name_ar'),$body_ar,$icon,$data,messageType: 'enter-room');

    }
}
