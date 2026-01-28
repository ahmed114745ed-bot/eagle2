<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\GameWallet;
use Illuminate\Bus\Queueable;
use App\Facades\CustomNotification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Public\Http\Services\UserCounterServices;

class FollowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $receiver;
    private $user;

    public function __construct($user, $receiver)
    {
        $this->user = $user;
        $this->receiver = $receiver;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->user->followBack($this->receiver)) {
            $receiverStatus = [
                'friend' => $this->receiver->friend + 1,
                'follower' => $this->receiver->follower + 1,
            ];
            $userStatus = [
                'friend' => $this->user->friend + 1,
                'following' => $this->user->following + 1,
            ];
            CustomNotification::followBack($this->receiver, $this->user);
            (new UserCounterServices)->UpgradeDateForType($this->receiver, 'friend');
            (new UserCounterServices)->eventUser($this->receiver, 'friend', 1);
        } else {

            $receiverStatus = [
                'follower' => $this->receiver->follower + 1,
            ];
            $userStatus = [
                'following' => $this->user->following + 1,
            ];
            CustomNotification::follow($this->receiver, $this->user);
            (new UserCounterServices)->UpgradeDateForType($this->receiver, 'followeds');
            (new UserCounterServices)->eventUser($this->receiver, 'follow', 1);
        }

        User::findOrFail($this->receiver->id)->update($receiverStatus);
        User::findOrFail($this->user->id)->update($userStatus);
        // $this->userRepository->update($receiverStatus, );
        // $this->userRepository->update($userStatus, $user->id);
        (new UserCounterServices)->eventUser($this->receiver, 'follower', 1);
    }
}
