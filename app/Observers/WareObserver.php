<?php

namespace App\Observers;

use App\Models\Ware;

class WareObserver
{
    /**
     * Handle the Ware "created" event.
     *
     * @param  \App\Models\Ware  $ware
     * @return void
     */
    public function created(Ware $ware)
    {
        if ($ware->enable){
            if ($ware->type = 6 ){
                settings()->set('intro_updated_at', time());
            }elseif ($ware->type = 4){
                settings()->set('frame_updated_at', time());
            }elseif ($ware->type = 1){
                settings()->set('extra_updated_at', time());
            }
        }

    }

    /**
     * Handle the Ware "updated" event.
     *
     * @param  \App\Models\Ware  $ware
     * @return void
     */
    public function updated(Ware $ware)
    {
        $isEnableOld = $ware->getOriginal('enable');
        $svgOld = $ware->getOriginal('img2');

        if ((!$isEnableOld && $ware->enable) ||($isEnableOld && !$ware->enable) || $svgOld != $ware->img2){
            if ($ware->type == 6 ){
                settings()->set('intro_updated_at', time());
            }elseif ($ware->type == 4){
                settings()->set('frame_updated_at', time());
            }elseif ($ware->type == 1){
                settings()->set('extra_updated_at', time());
            }elseif ($ware->type == 28){
                settings()->set('is_profile_frame_updated', time());
            }
        }
    }

    /**
     * Handle the Ware "deleted" event.
     *
     * @param  \App\Models\Ware  $ware
     * @return void
     */
    public function deleted(Ware $ware)
    {
        if ($ware->enable){
            if ($ware->type = 6 ){
                settings()->set('intro_updated_at', time());
            }elseif ($ware->type = 4){
                settings()->set('frame_updated_at', time());
            }elseif ($ware->type = 1){
                settings()->set('extra_updated_at', time());
            }
        }
    }

    /**
     * Handle the Ware "restored" event.
     *
     * @param  \App\Models\Ware  $ware
     * @return void
     */
    public function restored(Ware $ware)
    {
        //
    }

    /**
     * Handle the Ware "force deleted" event.
     *
     * @param  \App\Models\Ware  $ware
     * @return void
     */
    public function forceDeleted(Ware $ware)
    {
        //
    }
}
