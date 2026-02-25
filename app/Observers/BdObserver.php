<?php

namespace App\Observers;

use App\Models\Bd;
use App\Models\User;
use Modules\Milestones\Helpers\MilestoneHelper;



class BdObserver
{

    public function created(Bd $bd)
    {
        $userAppId = $bd->app_id;
        $userApp = User::find($userAppId);
        if (isset($userApp)) {
            $userApp->is_bd = 1;
            $userApp->save();
            MilestoneHelper::grantMilestoneToUser($userApp, 'bd');
        }
    }


    public function updated(Bd $bd) {}

    /**
     * Handle the Agency "deleted" event.
     *
     * @return void
     */
    public function deleted(Bd $bd)
    {

        $userAppId = $bd->app_id;
        $userApp = User::find($userAppId);
        if (isset($userApp)) {
            $userApp->is_bd = 0;
            $userApp->save();
            MilestoneHelper::removeReward($userApp, 'bd');
        }
    }
}
