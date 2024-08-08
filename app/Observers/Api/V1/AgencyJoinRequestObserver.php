<?php

namespace App\Observers\Api\V1;


use Carbon\Carbon;
use App\Models\User;
use App\Models\Agency;
use App\Helpers\Common;
use App\Models\GiftLog;
use App\Models\AgencyJoinRequest;
use Illuminate\Support\Facades\DB;
use App\Facades\CustomNotification;

class AgencyJoinRequestObserver
{
    /**
     * Handle the AgencyJoinRequest "created" event.
     *
     * @param  \App\Models\AgencyJoinRequest  $agencyJoinRequest
     * @return void
     */
    public function created(AgencyJoinRequest $agencyJoinRequest)
    {
        //
    }

    /**
     * Handle the AgencyJoinRequest "updated" event.
     *
     * @param  \App\Models\AgencyJoinRequest  $agencyJoinRequest
     * @return void
     */
    public function updated(AgencyJoinRequest $agencyJoinRequest)
    {
        //
    }

    /**
     * Handle the AgencyJoinRequest "deleted" event.
     *
     * @param  \App\Models\AgencyJoinRequest  $agencyJoinRequest
     * @return void
     */
    public function deleted(AgencyJoinRequest $agencyJoinRequest)
    {
        //
    }

    /**
     * Handle the AgencyJoinRequest "restored" event.
     *
     * @param  \App\Models\AgencyJoinRequest  $agencyJoinRequest
     * @return void
     */
    public function restored(AgencyJoinRequest $agencyJoinRequest)
    {
        //
    }

    /**
     * Handle the AgencyJoinRequest "force deleted" event.
     *
     * @param  \App\Models\AgencyJoinRequest  $agencyJoinRequest
     * @return void
     */
    public function forceDeleted(AgencyJoinRequest $agencyJoinRequest)
    {
        //
    }

    public function updating(AgencyJoinRequest $agencyJoinRequest){
        if ($agencyJoinRequest->status == 1){
            $user = User::query ()->find ($agencyJoinRequest->user_id);
            if ($user){
                $user->agency_id = $agencyJoinRequest->agency_id;
                $user->monthly_diamond_received = 0;
                $user->type_user = 1;
                $user->save ();
                $agid = $user->agency_id;
                $agency = Agency::find($agid);

                if ($agid == '' || $agid == null || $agid == 0){
                    $user->monthly_diamond_received = 0;
                    $user->coins = 0;
                    $user->save ();
                }
                // CustomNotification::acceptAgency($agency, $user);
            }
        }
    }
}
