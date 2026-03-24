<?php

namespace App\Observers;

use App\Facades\UserHandling;
use App\Models\Admin;
use App\Models\Agency;
use App\Models\AgencyJoinRequest;
use App\Models\MonthlyDiamondReceive;
use App\Models\User;
use App\Models\UsersJoinedAgency;
use Modules\Milestones\Helpers\MilestoneHelper;

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
        User::query()->where('id', $agency->app_owner_id)->update($updateData);

        $updateDataMonth['monthly_diamond_received'] = 0;
        $userDiamond = MonthlyDiamondReceive::query()->where('user_id', $agency->app_owner_id)->where('month', now()->month)->where('year', now()->year)->first();
        if ($userDiamond) $userDiamond->update($updateDataMonth);
        $user = User::find($agency->app_owner_id);

        MilestoneHelper::grantMilestoneToUser($user, 'host-agency-owner');
        $exists = UsersJoinedAgency::where([
            'user_id' =>  $user->id,
            'agency_id' => $agency->id,
            'type' => 1,
        ])->whereNull('leave_date')->exists();

        if (!$exists) {
            UsersJoinedAgency::create([
                'user_id' => $user->id,
                'agency_id' => $agency->id,
                'type' => 1,
                'join_date' => now(),
                'status' => 'Joined',
            ]);
        }

        info('create host-agency-owner milestone');
    }

    /**
     * Handle the Agency "updated" event.
     *
     * @return void
     */
    public function updated(Agency $agency)
    {
        if (!$agency->wasChanged('app_owner_id')) {
            return;
        }

        $originalOwnerId = $agency->getOriginal('app_owner_id');
        $newOwnerId = $agency->app_owner_id;

        if ($originalOwnerId) {
            $oldOwner = User::find($originalOwnerId);
            if ($oldOwner) {
                $oldOwner->update(['agency_id' => 0, 'is_host'  => 0]);
                MilestoneHelper::removeReward($oldOwner, 'host-agency-owner');
                $this->updatePreviousAgencyJoined($oldOwner, $agency->id);
                info('update host-agency-owner milestone');
            }
        }

        if ($newOwnerId) {
            $newUser = User::find($newOwnerId);
            if ($newUser) {
                $newUser->update(['agency_id' => $agency->id, 'is_host'  => 1]);
                MilestoneHelper::grantMilestoneToUser($newUser, 'host-agency-owner');

                $exists = UsersJoinedAgency::where([
                    'user_id' =>  $newUser->id,
                    'agency_id' => $agency->id,
                    'type' => 1,
                ])->whereNull('leave_date')->exists();

                if (!$exists) {
                    UsersJoinedAgency::create([
                        'user_id' => $newUser->id,
                        'agency_id' => $agency->id,
                        'type' => 1,
                        'join_date' => now(),
                        'status' => 'Joined',
                    ]);
                }
                info('update 2 host-agency-owner milestone');
            }
        }

        User::query()->where('id', $agency->app_owner_id)->update(['agency_id' => $agency->id]);
    }

    /**
     * Handle the Agency "deleted" event.
     *
     * @return void
     */
    public function deleted(Agency $agency)
    {
        //            if ($agency->Host_agency) {
        AgencyJoinRequest::query()->where('agency_id', $agency->id)->delete();
        UserHandling::kickOfAllUsersFromAgency($agency);
        User::query()->where('agency_id', $agency->id)->update(['agency_id' => 0, 'type_user' => 0]);
        $joinedAgency = UsersJoinedAgency::where(['agency_id' => $agency->id])->get();
        if ($joinedAgency) UsersJoinedAgency::where('agency_id', $agency->id)->update(['leave_date' => now(), 'status' => 'delete agency from admin']);
        $user = User::find($agency->app_owner_id);
        Admin::where('username', $user->uuid)->delete();
        MilestoneHelper::removeReward($user, 'host-agency-owner');
        info('delete host-agency-owner milestone');
        //            }
    }


    private function updatePreviousAgencyJoined(User $user, $agencyId)
    {
        $checkAgencyUser = UsersJoinedAgency::where([
            'user_id' => $user->id,
            'agency_id' => $agencyId,
        ])->whereNull('leave_date')->first();

        if (!$checkAgencyUser) {
            UsersJoinedAgency::create([
                'user_id' => $user->id,
                'agency_id' => $agencyId,
                'type' => 2,
                'join_date' => now(),
                'leave_date' => now(),
                'status' => 'change agency by admin',
                'kicked_by_admin' => Auth::id(),
            ]);
        } else {
            $checkAgencyUser->update([
                'leave_date' => now(),
                'status' => 'change agency by admin',
                'kicked_by_admin' => Auth::id()
            ]);
        }
    }
}
