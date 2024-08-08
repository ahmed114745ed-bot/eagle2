<?php

namespace App\Observers;

use App\Models\Gift;

class GiftObserver
{
    /**
     * Handle the Gift "created" event.
     *
     * @param  \App\Models\Gift  $gift
     * @return void
     */
    public function created(Gift $gift)
    {
        if ($gift->enable){
            settings()->set('gifts_update_at', time());

        }
    }

    /**
     * Handle the Gift "updated" event.
     *
     * @param  \App\Models\Gift  $gift
     * @return void
     */
    public function updated(Gift $gift)
    {
        $isEnableOld = $gift->getOriginal('enable');
        $svgOld = $gift->getOriginal('show_img');

        if ((!$isEnableOld && $gift->enable)|| ($isEnableOld && !$gift->enable) || $gift->show_img != $svgOld){
            settings()->set('gifts_update_at', time());
        }
    }

    /**
     * Handle the Gift "deleted" event.
     *
     * @param  \App\Models\Gift  $gift
     * @return void
     */
    public function deleted(Gift $gift)
    {
        if ($gift->enable){
            settings()->set('gifts_update_at', time());

        }
    }

    /**
     * Handle the Gift "restored" event.
     *
     * @param  \App\Models\Gift  $gift
     * @return void
     */
    public function restored(Gift $gift)
    {
        //
    }

    /**
     * Handle the Gift "force deleted" event.
     *
     * @param  \App\Models\Gift  $gift
     * @return void
     */
    public function forceDeleted(Gift $gift)
    {
        //
    }
}
