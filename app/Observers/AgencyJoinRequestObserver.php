<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Agency;
use App\Models\AgencyJoinRequest;

class AgencyJoinRequestObserver
{
    public function updating(AgencyJoinRequest $agencyJoinRequest)
    {
        if ($agencyJoinRequest->status == 1) {
            $user = User::query()->find($agencyJoinRequest->user_id);
            if ($user) {
                $user->agency_id = $agencyJoinRequest->agency_id;

                $user->type_user = 1;
                $user->save();
                uploadMonthlyDiamondReceive($user->id, 0);
                $agid = $user->agency_id;

                if ($agid == '' || $agid == null || $agid == 0) {
                    $user->coins = 0;
                    $user->save();
                    uploadMonthlyDiamondReceive($user->id, 0);
                }
                // CustomNotification::acceptAgency($agency, $user);
            }
        }
    }
}
