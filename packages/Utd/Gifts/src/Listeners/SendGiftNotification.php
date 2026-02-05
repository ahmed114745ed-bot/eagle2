<?php

namespace Utd\Gifts\Listeners;

use Utd\Gifts\Events\GiftSent;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Helpers\CustomNotification;

class SendGiftNotification implements ShouldQueue
{
    public $queue = 'notifications';

    public function handle(GiftSent $event): void
    {
        foreach ($event->logs as $log) {
            // Send notification based on source type
            match($event->dto->sourceType) {
                'room' => $this->notifyRoomGift($event, $log),
                'moment' => $this->notifyMomentGift($event, $log),
                'reel' => $this->notifyReelGift($event, $log),
                default => null,
            };
        }
    }

    private function notifyRoomGift($event, $log): void
    {
        // Room-specific notification logic
        // يمكن استدعاء Room package notification
    }

    private function notifyMomentGift($event, $log): void
    {
        // Moment-specific notification
        if (class_exists(CustomNotification::class)) {
            CustomNotification::sendMomentGift(
                $event->sender,
                $event->gift,
                $log->receiver,
                $event->dto->getMomentId()
            );
        }
    }

    private function notifyReelGift($event, $log): void
    {
        // Reel-specific notification
    }
}
