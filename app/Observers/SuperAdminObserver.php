<?php

namespace App\Observers;

use App\Models\User;
use Modules\AreaManager\Entities\AreaManager;
use Modules\Milestones\Helpers\MilestoneHelper;
use Modules\SuperAdmin\Entities\SuperAdmin;



class SuperAdminObserver
{

    public function created(SuperAdmin $superAdmin)
    {
        $userAppId = $superAdmin->app_id;
        $userApp = User::find($userAppId);
        if (isset($userApp)) {
            $userApp->is_super_admin = 1;
            $userApp->save();
            MilestoneHelper::grantMilestoneToUser($userApp->id, 'super-admin');
        }
    }


    public function updated(SuperAdmin $superAdmin) {}

    /**
     * Handle the Agency "deleted" event.
     *
     * @return void
     */
    public function deleted(SuperAdmin $superAdmin)
    {

        $userAppId = $superAdmin->app_id;
        $userApp = User::find($userAppId);
        if (isset($userApp)) {
            $userApp->is_super_admin = 0;
            $userApp->save();
            MilestoneHelper::removeReward($userApp, 'super-admin');
        }
    }
}
