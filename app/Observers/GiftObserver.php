<?php

namespace App\Observers;

use App\Models\Gift;

class GiftObserver
{
    /**
     * Handle the Gift "created" event.
     */
    public function created(Gift $gift): void
    {
        if ($gift->enable) {
            settings()->set('gifts_update_at', time());
        }
    }

    /**
     * Handle the Gift "updated" event.
     */
    public function updated(Gift $gift): void
    {
        $isEnableOld = $gift->getOriginal('enable');
        $svgOld = $gift->getOriginal('show_img');

        if ((!$isEnableOld && $gift->enable) || ($isEnableOld && !$gift->enable) || $gift->show_img != $svgOld) {
            settings()->set('gifts_update_at', time());
        }
    }

    /**
     * Handle the Gift "deleted" event.
     */
    public function deleted(Gift $gift): void
    {
        if ($gift->enable) {
            settings()->set('gifts_update_at', time());
        }
    }
}
