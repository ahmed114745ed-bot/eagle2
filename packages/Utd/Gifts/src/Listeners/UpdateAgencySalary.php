<?php

namespace Utd\Gifts\Listeners;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Utd\Agency\Services\AgencySalaryService;
use Utd\Gifts\Events\GiftSent;

class UpdateAgencySalary implements ShouldQueue
{
    public $queue = 'salary-processing';

    public function handle(GiftSent $event): void
    {
        // Only for room gifts
        if (! $event->isRoomGift()) {
            return;
        }

        foreach ($event->getReceiverIds() as $receiverId) {
            $user = User::find($receiverId);

            if ($user && ($user->agency_id ?? null)) {
                $this->updateSalary($user, $event->getPricePerReceiver());
            }
        }
    }

    private function updateSalary($user, int $amount): void
    {
        $salaryService = app(AgencySalaryService::class);
        $salaryService->updateFromGift($user, $amount);
    }
}
