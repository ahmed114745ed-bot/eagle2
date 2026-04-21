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

class UpdateUsersAndSendCharismaToZigo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public Room $room, public array $userIds, public int $earnedCoinsPerUser, public int $userId)
    {

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data =
            (new UserCharismaService())->addTotalEarnedCoinsInUserRoom($this->room, $this->userIds, $this->earnedCoinsPerUser);

        if (UserCharismaService::FORMAT_TOTAL_IN_SERVICE) {
            $data = array_map(function($user) {
                if (isset($user['total'])) {
                    $user['total'] = numToStringNew($user['total']);
                }
                return $user;
            }, $data);
        }

        $ms = [
            'messageContent' => [
                "message" => "updateCharisma",
                "data" => $data,
            ]
        ];
        $json = json_encode ($ms);

        Common::sendToZego('SendCustomCommand', $this->room->id, $this->userId, $json);

    }
}
