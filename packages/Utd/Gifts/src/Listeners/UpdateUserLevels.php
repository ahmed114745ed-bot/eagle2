<?php

namespace Utd\Gifts\Listeners;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Utd\Gifts\Events\GiftSent;
use Utd\Level\Services\LevelService;

class UpdateUserLevels implements ShouldQueue
{
    public $queue = 'gift-processing';

    public function handle(GiftSent $event): void
    {
        $sender = $event->sender;
        if ($sender) {
            $this->updateLevel($sender);
        }
        foreach ($event->getReceiverIds() as $receiverId) {
            $receiver = User::find($receiverId);
            if ($receiver) {
                $this->updateLevel($receiver);
            }
        }
    }

    private function updateLevel($user): void
    {
        $levelService = app(LevelService::class);
        $levelService->recalculate($user);
    }
}
