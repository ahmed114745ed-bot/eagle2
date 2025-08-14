<?php

namespace Modules\RoomBoom\Jobs;

use App\Helpers\Common;
use App\Helpers\UserCommon;
use App\Models\GiftLog;
use App\Models\Room;
use App\Models\User;
use App\Models\UserGift;
use Carbon\Carbon;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Achievement\Entities\UserAchievementLevel;
use Modules\RoomBoom\Entities\RoomBoom;
use Modules\RoomBoom\Entities\RoomBoomReward;

class EndBoomZegoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $currentLevel;
    public $roomId;
    public $roomUid;

    public function __construct($currentLevel, $roomId, $roomUid)
    {
        $this->currentLevel = $currentLevel;
        $this->roomId = $roomId;
        $this->roomUid = $roomUid;
    }

    public function handle(): void
    {
        $this->send(30);

        sleep(20);

        $this->send(10);
    }

    public function send($duration): void
    {
        $d = [
            "messageContent" => [
                "message" => "roomBoomEnded",
                'roomBoomLevel' => $this->currentLevel,
                'duration' => $duration,
            ]
        ];
        $json = json_encode($d);

        Common::sendToZego('SendCustomCommand', $this->roomId, $this->roomUid, $json);
    }
}
