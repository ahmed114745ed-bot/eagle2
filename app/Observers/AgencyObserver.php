<?php

namespace App\Observers;

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
     * @return void
     */
    public function created(Agency $agency)
    {
        $updateData = [
            'agency_id' => $agency->id,
        ];

        if ($agency->Host_agency) {
            $updateData['monthly_diamond_received'] = 0;
        }

        User::query()
            ->where('id', $agency->app_owner_id)
            ->update($updateData);
    }

    /**
     * Handle the Agency "updated" event.
     *
     * @return void
     */
    public function updated(Agency $agency)
    {
        User::query()->where('id', $agency->app_owner_id)->update(['agency_id' => $agency->id]);
    }

    /**
     * Handle the Agency "deleted" event.
     *
     * @return void
     */
    public function deleted(Agency $agency)
    {
        if ($agency->Host_agency) {
            AgencyJoinRequest::query()->where('agency_id', $agency->id)->delete();
            UserHandling::kickOfAllUsersFromAgency($agency);
            User::query()->where('agency_id', $agency->id)->update(['agency_id' => 0, 'type_user' => 0]);
            $joinedAgency = UsersJoinedAgency::where(['agency_id' =>   $agency->id])->get();
            if ($joinedAgency) UsersJoinedAgency::where('agency_id',  $agency->id)->update(['leave_date' => now(),'status' =>'delete agency from admin']);
            $user = User::find($agency->app_owner_id);
            Admin::where('username', $user->uuid)->delete();
        }
    }
}
