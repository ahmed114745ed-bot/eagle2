<?php

namespace Utd\Charizma\Jobs;

use App\Helpers\Common;
use Utd\Room\Entities\Room;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Utd\Charizma\Services\UserCharismaService;

class UpdateUsersAndSendCharismaToZigo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Room $room,
        public array $userIds,
        public int $earnedCoinsPerUser,
        public int $userId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $data = (new UserCharismaService())->addTotalEarnedCoinsInUserRoom(
            $this->room,
            $this->userIds,
            $this->earnedCoinsPerUser
        );

        $ms = [
            'messageContent' => [
                "message" => "updateCharisma",
                "data" => $data,
            ]
        ];
        $json = json_encode($ms);

        Common::sendToZego('SendCustomCommand', $this->room->id, $this->userId, $json);
    }
}
