<?php

namespace App\Jobs;

use App\Helpers\Common;
use App\Models\Room;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class SendGiftToZegoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $zigoData;
    private $totalPrice;
    private $isToZego;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($zigoData, $totalPrice, $isToZego)
    {
        //
        $this->zigoData   = $zigoData;
        $this->totalPrice = $totalPrice;
        $this->isToZego = $isToZego;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->sendZigoGifts($this->zigoData);
    }

    /**
     * @param $zigoData
     * @return void
     */
    public function sendZigoGifts($zigoData): void
    {
        Common::sendToZego_2('SendBroadcastMessage', $zigoData['room_id'], $zigoData['sender_id'], $zigoData['from_name'], "  {$zigoData['number']} x ارسل هدية  " . " قيمتها {$zigoData['gift_price']} " . " الى {$zigoData['to_name']}");
        if ($this->isToZego) {
            $d    = [
                "messageContent" => [
                    "message"     => "showGifts",
                    "showGift"    => $zigoData['show_gift'],
                    'giftImg'     => $zigoData['gift_img'],
                    'gift_id'     => $zigoData['gift_id'],
                    'send_id'     => $zigoData['sender_id'],
                    'receiver_id' => $zigoData['receiver_id'],
                    'isExpensive' => $this->totalPrice >= 2000,
                    'num_gift'    => $zigoData['number'],
                    "plural"      => $zigoData['plural'],
                    'gift_price'  => $this->totalPrice,
                    'coins'  => @$zigoData['coins'] ?? '0',

                ]
            ];
            $json = json_encode($d);
            Common::sendToZego('SendCustomCommand', $zigoData['room_id'], $zigoData['sender_id'], $json);
            if ($this->totalPrice >= 2000) {
                $d     = [
                    "messageContent" => [
                        "message"     => "showBanner",
                        'send_id'     => (integer)$zigoData['sender_id'],
                        'receiver_id' => (integer)$zigoData['receiver_id'],
                        'owner_id'    => (integer)$zigoData['owner_id'],
                        'is_password' => $zigoData['is_password'],
                        "giftImg"     => $zigoData['gift_img']
                    ]
                ];
                $json  = json_encode($d);
                $this->dispatchJobToQueue(new AllOpeningRoomsZegoRequest($json, $zigoData['sender_id'],$zigoData['room_id']), 'heavyProcessing');
            }
        }
    }

    function dispatchJobToQueue($job, $queueName = 'database')
    {
        $queueNames = config('queue.connections.' . $queueName .'.queue');

        $minQueueSize  = null;
        $selectedQueue = null;

        foreach ($queueNames as $queueName) {
            $queueSize = \DB::table('jobs')->where('queue', $queueName)->count();
            //            $jobCount  = \DB::table('job_statistics')->where('queue', $queueName)->value('job_count');

            if ($minQueueSize === null || $queueSize  < $minQueueSize) {
                $minQueueSize  = $queueSize ;
                $selectedQueue = $queueName;
            }
        }

        \Illuminate\Support\Facades\Queue::pushOn($selectedQueue, $job);
    }
}
