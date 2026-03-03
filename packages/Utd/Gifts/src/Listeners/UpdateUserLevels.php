<?php

namespace Utd\Gifts\Listeners;

use Utd\Gifts\Events\GiftSent;
use Utd\Gifts\Support\ModelResolver;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * UpdateUserLevels
 *
 */
class UpdateUserLevels implements ShouldQueue
{
    public $queue = 'gift-processing';

    public function handle(GiftSent $event): void
    {
        $userModel = ModelResolver::getUserModel();
        if (!$userModel) {
            return;
        }

        $sender = $event->sender;
        if ($sender) {
            $this->updateLevel($sender);
        }
        foreach ($event->getReceiverIds() as $receiverId) {
            $receiver = $userModel::find($receiverId);
            if ($receiver) {
                $this->updateLevel($receiver);
            }
        }
    }

    private function updateLevel($user): void
    {
        if (class_exists('\Utd\Level\Services\LevelService')) {
            $levelService = app('\Utd\Level\Services\LevelService');
            $levelService->recalculate($user);
            return;
        }

        if (method_exists($user, 'updateLevel')) {
            $user->updateLevel();
        }
    }
}
