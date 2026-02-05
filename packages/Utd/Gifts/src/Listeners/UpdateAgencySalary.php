<?php

namespace Utd\Gifts\Listeners;

use Utd\Gifts\Events\GiftSent;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\User;

class UpdateAgencySalary implements ShouldQueue
{
    public $queue = 'salary-processing';

    public function handle(GiftSent $event): void
    {
        // Only for room gifts
        if (!$event->isRoomGift()) {
            return;
        }

        foreach ($event->getReceiverIds() as $receiverId) {
            $user = User::find($receiverId);
            
            if ($user && $user->agency_id) {
                // Update agency salary logic
                $this->updateSalary($user, $event->getPricePerReceiver());
            }
        }
    }

    private function updateSalary(User $user, int $amount): void
    {
        // Agency salary update logic
        // يمكن استدعاء Agency package service
    }
}
