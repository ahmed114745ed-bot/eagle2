<?php

namespace App\Jobs;

use App\Helpers\Common;
use App\Interfaces\RoomJobInterface;
use GuzzleHttp\Promise\Utils;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TestTestCharizma implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var false
     */
    private $data;
    public RoomJobInterface $roomJob;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data,RoomJobInterface $roomJob)
    {
        //
        $this->data   = $data ?? [];
        $this->roomJob   = $roomJob;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $promises = [];
        \Illuminate\Support\Facades\Log::channel('charisma_value')->info('[CHARISMA][4-RTM] TestTestCharizma job started', [
            'data_count' => count($this->data),
            'data'       => $this->data,
        ]);

        foreach ($this->data as $key => $value){
            [$data, $roomId, $userId] = $this->roomJob->getVariables($value);

            \Illuminate\Support\Facades\Log::channel('charisma_value')->info('[CHARISMA][4-RTM] Sending to RTM for room', [
                'room_id'  => $roomId,
                'user_id'  => $userId,
                'data'     => $data,
            ]);

            $json = $this->roomJob->sendToZego($data, $roomId, $userId ?? 0);

            \Illuminate\Support\Facades\Log::channel('charisma_value')->info('[CHARISMA][4-RTM] JSON prepared for RTM', [
                'room_id' => $roomId,
                'user_id' => $userId,
                'json'    => $json,
            ]);

            $promise = Common::sendToZego3('SendCustomCommand', $roomId, $userId ?? 0, [$json]);
            $promises = array_merge($promises, $promise);
        }

        \Illuminate\Support\Facades\Log::channel('charisma_value')->info('[CHARISMA][4-RTM] All RTM promises dispatched', [
            'promises_count' => count($promises),
        ]);

        Utils::unwrap($promises);
    }
}
