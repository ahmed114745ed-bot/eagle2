<?php

namespace App\Jobs;

use App\Helpers\Common;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Modules\Charizma\Http\Services\UserCharismaService;

class ResetCharisma implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private int $roomId)
    {

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        (new UserCharismaService())->removeRoomCharisma($this->roomId);

        $ms = [
            'messageContent' => [
                "message" =>  'closeCharisma',
            ]
        ];
        $json = json_encode($ms);

        Common::sendToZego('SendCustomCommand', $this->roomId, 0, $json);
    }
}
