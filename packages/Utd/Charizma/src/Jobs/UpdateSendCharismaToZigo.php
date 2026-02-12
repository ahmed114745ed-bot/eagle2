<?php

namespace Utd\Charizma\Jobs;

use App\Helpers\Common;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Utd\Charizma\Services\UserCharismaService;
use Utd\Room\Entities\Room;

class UpdateSendCharismaToZigo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $roomId,
        public array $userIds,
        public int $earnedCoinsPerUser,
        public int $userId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $room = Room::where(['id' => $this->roomId])
            ->selectRaw('id,uid,room_visitor,play_num,hot,room_pass,session,microphone,charizma_status')
            ->first();

        $data = (new UserCharismaService())->addTotalEarnedCoinsInUserRoom(
            $room,
            $this->userIds,
            $this->earnedCoinsPerUser
        );

        $ms = [
            'messageContent' => [
                'message' => 'updateCharisma',
                'data' => $data,
            ],
        ];
        $json = json_encode($ms);

        Common::sendToZego('SendCustomCommand', $room->id, $this->userId, $json);
    }
}
