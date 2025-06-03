<?php

namespace Modules\Charizma\Jobs;

use App\Helpers\Common;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Charizma\Http\Services\UserCharismaService;

class UpdateSendCharismaToZigo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public int $roomId, public array $userIds, public int $earnedCoinsPerUser, public int $userId)
    {

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $room = Room::where(['uid' => $this->roomId])->selectRaw('id,uid,room_visitor,play_num,hot,room_pass,session,microphone,charizma_status')->first();
        $data =
            (new UserCharismaService())->addTotalEarnedCoinsInUserRoom($room, $this->userIds, $this->earnedCoinsPerUser);
        $ms = [
            'messageContent' => [
                "message" => "updateCharisma",
                "data" => $data,
            ]
        ];
        $json = json_encode ($ms);

        Common::sendToZego('SendCustomCommand', $room->id, $this->userId, $json);

    }
}
