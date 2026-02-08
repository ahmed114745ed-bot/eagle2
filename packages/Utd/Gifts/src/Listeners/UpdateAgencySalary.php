<?php

namespace Utd\Gifts\Listeners;

use Utd\Gifts\Events\GiftSent;
use Utd\Gifts\Support\ModelResolver;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateAgencySalary implements ShouldQueue
{
    public $queue = 'salary-processing';

    public function handle(GiftSent $event): void
    {
        // Only for room gifts
        if (!$event->isRoomGift()) {
            return;
        }
        
        $userModel = ModelResolver::getUserModel();
        if (!$userModel) {
            return;
        }

        foreach ($event->getReceiverIds() as $receiverId) {
            $user = $userModel::find($receiverId);
            
            if ($user && ($user->agency_id ?? null)) {
                // Update agency salary logic
                $this->updateSalary($user, $event->getPricePerReceiver());
            }
        }
    }

    private function updateSalary($user, int $amount): void
    {
        // Agency salary update logic
        // يمكن استدعاء Agency package service إذا كان موجود
        if (class_exists('\Utd\Agency\Services\AgencySalaryService')) {
            $salaryService = app('\Utd\Agency\Services\AgencySalaryService');
            $salaryService->updateFromGift($user, $amount);
        }
    }
}
