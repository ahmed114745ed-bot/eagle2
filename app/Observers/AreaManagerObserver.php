<?php

namespace App\Observers;

use App\Models\User;
use Modules\AreaManager\Entities\AreaManager;
use Modules\Milestones\Helpers\MilestoneHelper;



class AreaManagerObserver
{

    public function created(AreaManager $areaManager)
    {
        $userAppId = $areaManager->app_id;
        $userApp = User::find($userAppId);
        if (isset($userApp)) {
           $userApp->is_area_manager = 1;
            $userApp->save();
           
             MilestoneHelper::grantMilestoneToUser($userApp->id, 'area-manager');
        }
    }


    public function updated(AreaManager $areaManager) {}

    /**
     * Handle the Agency "deleted" event.
     *
     * @return void
     */
    public function deleted(AreaManager $areaManager)
    {

        $userAppId = $areaManager->app_id;
        $userApp = User::find($userAppId);
        if (isset($userApp)) {
            $userApp->is_area_manager = 0;
            $userApp->save();
            MilestoneHelper::removeReward($userApp, 'area-manager');
        }
    }
}