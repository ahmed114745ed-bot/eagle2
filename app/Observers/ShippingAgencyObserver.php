<?php

namespace App\Observers;


use App\Models\ShippingAgency;
use App\Models\User;

use Modules\Milestones\Helpers\MilestoneHelper;

class ShippingAgencyObserver
{
    /**
     * Handle the Agency "created" event.
     *
     * @return void
     */
    public function created(ShippingAgency $agency)
    {

        $user = User::find($agency->app_owner_id);
        if ($user) MilestoneHelper::grantMilestoneToUser($user, 'charge-agency-owner');
    }

    /**
     * Handle the Agency "updated" event.
     *
     * @return void
     */


    /**
     * Handle the Agency "deleted" event.
     *
     * @return void
     */
    public function deleted(ShippingAgency $agency)
    {
        $user = User::find($agency->app_owner_id);
        if ($user) MilestoneHelper::removeReward($user, 'charge-agency-owner');
    }
}
