<?php

namespace App\Observers\Api\V1;

use App\Models\User;
use App\Models\Admin;
use App\Models\Agency;
use App\Facades\UserHandling;
use App\Models\AgencyJoinRequest;
use App\Models\UsersJoinedAgency;

class AgencyObserver
{
    /**
     * Handle the Agency "created" event.
     *
     * @param  \App\Models\Agency  $agency
     * @return void
     */
    public function created(Agency $agency)
    {
        User::query()->where('id', $agency->app_owner_id)->update(['agency_id' => $agency->id]);
    }


    /**
     * Handle the Agency "updated" event.
     *
     * @param  \App\Models\Agency  $agency
     * @return void
     */
    public function updated(Agency $agency)
    {
        User::query()->where('id', $agency->app_owner_id)->update(['agency_id' => $agency->id]);
    }


    /**
     * Handle the Agency "deleted" event.
     *
     * @param  \App\Models\Agency  $agency
     * @return void
     */
    public function deleted(Agency $agency)
    {
        User::query()->where('agency_id', $agency->id)->update(['agency_id' => 0, 'type_user' => 0]);
        AgencyJoinRequest::query()->where('agency_id', $agency->id)->delete();
        UserHandling::kickOfAllUsersFromAgency($agency);
        $joinedAgency = UsersJoinedAgency::where(['agency_id' =>   $agency->id])->get();
        if ($joinedAgency) UsersJoinedAgency::where('agency_id',  $agency->id)->update(['leave_date' => now()]);
        $user = User::find($agency->app_owner_id);
        Admin::where('username', $user->uuid)->delete();
        //check delete agency action 
    }

    /**
     * Handle the Agency "restored" event.
     *
     * @param  \App\Models\Agency  $agency
     * @return void
     */
    public function restored(Agency $agency)
    {
        //
    }

    /**
     * Handle the Agency "force deleted" event.
     *
     * @param  \App\Models\Agency  $agency
     * @return void
     */
    public function forceDeleted(Agency $agency)
    {
        //
    }
}
