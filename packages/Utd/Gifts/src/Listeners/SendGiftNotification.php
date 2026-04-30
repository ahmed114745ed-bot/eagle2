<?php

namespace Utd\Gifts\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Utd\Gifts\Events\GiftSent;
use Utd\Gifts\Services\GiftsNotification;

class SendGiftNotification implements ShouldQueue
{
    public $queue = 'notifications';

    public function handle(GiftSent $event): void
    {
        foreach ($event->logs as $log) {
            match ($event->dto->sourceType) {
                'room' => $this->notifyRoomGift($event, $log),
                'moment' => $this->notifyMomentGift($event, $log),
                'reel' => $this->notifyReelGift($event, $log),
                default => null,
            };
        }
    }

    private function notifyRoomGift($event, $log): void {}

    private function notifyMomentGift($event, $log): void
    {
        (new GiftsNotification)->sendMomentGift(
            $event->sender,
            $event->gift,
            $log->receiver,
            $event->dto->getMomentId()
        );
    }

    private function notifyReelGift($event, $log): void {}
}
