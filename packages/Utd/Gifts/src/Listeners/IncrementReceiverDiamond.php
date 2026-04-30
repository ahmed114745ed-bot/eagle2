<?php

namespace Utd\Gifts\Listeners;

use App\Support\PackageHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Utd\Achievements\Jobs\IncreaseDiamondJob;
use Utd\Gifts\Events\GiftSent;

class IncrementReceiverDiamond implements ShouldQueue
{
    public $queue = 'gift-processing';

    public function handle(GiftSent $event): void
    {
        $pricePerReceiver = $event->getPricePerReceiver();

        if (PackageHelper::isInstalled('achievement')) {
            foreach ($event->getReceiverIds() as $receiverId) {
                IncreaseDiamondJob::dispatch($receiverId, $pricePerReceiver);
            }
        }
    }
}
