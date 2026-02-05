<?php

namespace Utd\Gifts\Listeners;

use Utd\Gifts\Events\GiftSent;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateUserLevels implements ShouldQueue
{
    public $queue = 'gift-processing';

    public function handle(GiftSent $event): void
    {
        // تحديث مستويات المستخدمين (sender & receivers)
        // يمكن استدعاء Achievement package أو Level system
    }
}
