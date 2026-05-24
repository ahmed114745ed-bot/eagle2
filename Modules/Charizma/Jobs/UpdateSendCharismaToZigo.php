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
use Illuminate\Support\Facades\Log;
use Modules\Charizma\Http\Services\UserCharismaService;

class UpdateSendCharismaToZigo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Number of seconds to wait before retrying the job.
     */
    public int $backoff = 2;

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
        $room = Room::where(['id' => $this->roomId])
            ->selectRaw('id,uid,play_num,hot,room_pass,session,microphone,charizma_status')
            ->first();

        if (!$room) {
            return;
        }

        if (!$room->charizma_status) {
            return;
        }

        $data = (new UserCharismaService())->addTotalEarnedCoinsInUserRoom($room, $this->userIds, $this->earnedCoinsPerUser);

        if (empty($data)) {
            return;
        }

        $data = array_map(function($user) {
            if (isset($user['total'])) {
                $user['total'] = UserCharismaService::formatTotalInService()
                    ? numToStringNew($user['total'])
                    : (int) $user['total'];
            }
            return $user;
        }, $data);

        $ms = [
            'messageContent' => [
                "message" => "updateCharisma",
                "data" => $data,
            ]
        ];
        $json = json_encode($ms);


        $response = Common::sendToZego('SendCustomCommand', $room->id, $this->userId, $json);

        if ($response === null || (isset($response['Code']) && $response['Code'] != 0)) {
            Log::error('UpdateSendCharismaToZigo: Zego API failed', [
                'roomId' => $this->roomId,
                'userId' => $this->userId,
                'response' => $response,
                'attempt' => $this->attempts(),
            ]);

            // Throw exception to trigger retry
            if ($this->attempts() < $this->tries) {
                throw new \Exception('Zego API call failed, retrying...');
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?\Throwable $exception): void
    {
        Log::error('UpdateSendCharismaToZigo: Job failed permanently', [
            'roomId' => $this->roomId,
            'userId' => $this->userId,
            'userIds' => $this->userIds,
            'exception' => $exception?->getMessage(),
        ]);
    }
}
