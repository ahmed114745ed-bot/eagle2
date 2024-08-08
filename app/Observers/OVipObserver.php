<?php

namespace App\Observers;

use App\Models\OVip;

class OVipObserver
{
    /**
     * Handle the OVip "created" event.
     *
     * @param  \App\Models\OVip  $oVip
     * @return void
     */
    public function created(OVip $oVip)
    {
        settings()->set('extra_updated_at', time());
    }

    /**
     * Handle the OVip "updated" event.
     *
     * @param  \App\Models\OVip  $oVip
     * @return void
     */
    public function updated(OVip $oVip)
    {
        if ($oVip->isDirty('img')){
            settings()->set('extra_updated_at', time());
        }
    }

    /**
     * Handle the OVip "deleted" event.
     *
     * @param  \App\Models\OVip  $oVip
     * @return void
     */
    public function deleted(OVip $oVip)
    {
        settings()->set('extra_updated_at', time());
    }

    /**
     * Handle the OVip "restored" event.
     *
     * @param  \App\Models\OVip  $oVip
     * @return void
     */
    public function restored(OVip $oVip)
    {
        //
    }

    /**
     * Handle the OVip "force deleted" event.
     *
     * @param  \App\Models\OVip  $oVip
     * @return void
     */
    public function forceDeleted(OVip $oVip)
    {
        //
    }
}
