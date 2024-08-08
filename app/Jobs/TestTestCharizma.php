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
        foreach ($this->data as $key => $value){
            [$data, $roomId, $userId] = $this->roomJob->getVariables($value);
            $josns[] =  $this->roomJob->sendToZego($data, $roomId, $userId ?? 0);
            $promise =  Common::sendToZego3('SendCustomCommand', $roomId, $userId ?? 0, $josns);
            $promises = array_merge($promises, $promise);
        }
        Utils::unwrap($promises);
    }
}
