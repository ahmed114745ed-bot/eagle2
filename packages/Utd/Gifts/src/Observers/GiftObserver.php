<?php

namespace Utd\Gifts\Observers;

use Utd\Gifts\Entities\Gift;

/**
 * GiftObserver
 * Observer للتعامل مع أحداث الهدايا
 */
class GiftObserver
{
    /**
     * Handle the Gift "created" event
     */
    public function created(Gift $gift): void
    {
        if ($gift->enable) {
            if (function_exists('settings')) {
                settings()->set('gifts_update_at', time());
            }
        }
    }

    /**
     * Handle the Gift "updated" event
     */
    public function updated(Gift $gift): void
    {
        $isEnableOld = $gift->getOriginal('enable');
        $svgOld = $gift->getOriginal('show_img');

        if ((! $isEnableOld && $gift->enable) || ($isEnableOld && ! $gift->enable) || $gift->show_img !== $svgOld) {
            if (function_exists('settings')) {
                settings()->set('gifts_update_at', time());
            }
        }
    }

    /**
     * Handle the Gift "deleted" event
     */
    public function deleted(Gift $gift): void
    {
        if ($gift->enable) {
            if (function_exists('settings')) {
                settings()->set('gifts_update_at', time());
            }
        }
    }
}
