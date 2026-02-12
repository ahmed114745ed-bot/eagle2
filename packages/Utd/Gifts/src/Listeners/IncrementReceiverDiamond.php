<?php

namespace Utd\Gifts\Listeners;

use App\Jobs\IncreaseDiamondJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Utd\Gifts\Events\GiftSent;

class IncrementReceiverDiamond implements ShouldQueue
{
    public $queue = 'gift-processing';

    public function handle(GiftSent $event): void
    {
        $pricePerReceiver = $event->getPricePerReceiver();

        foreach ($event->getReceiverIds() as $receiverId) {
            IncreaseDiamondJob::dispatch($receiverId, $pricePerReceiver);
        }
    }
}
