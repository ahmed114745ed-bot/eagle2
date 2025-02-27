<?php

namespace App\Observers\Api\V1;

use Carbon\Carbon;
use App\Models\Pack;
use App\Models\User;
use App\Models\Ware;
use App\Models\Agency;
use App\Models\Follow;
use App\Models\Target;
use App\Helpers\Common;
use App\Models\GiftLog;
use App\Models\LiveTime;
use App\Models\BlackList;
use App\Models\FamilyUser;
use App\Models\UserTarget;
use App\Models\Report_user;
use App\Models\UserSallary;
use App\Facades\UserHandling;
use App\Models\AgencySallary;
use App\Models\AgencyJoinRequest;
use App\Admin\Controllers\BlackListController;
use Illuminate\Validation\ValidationException;

class UserObserver
{
    /**
     * Handle the User "created" event.
     *
     * @param \App\Models\User $user
     * @return void
     */
    public function created(User $user)
    {
        /* $user->profile()->create(
            [
                'gender' => 1
            ]
        ); */
    }

    /**
     * Handle the User "updated" event.
     *
     * @param \App\Models\User $user
     * @return void
     */


    public function updated(User $user)
    {
        if (!$user->enableSaving) return;
        //
        //        if (!$user->agency_id) {
        //            AgencyJoinRequest::query()->where('user_id', $user->id)->delete();
        //        }
        //        if ($user->agency_id) {
        //            $agency = Agency::query()->find($user->agency_id);
        //            if (!$agency) {
        //                AgencyJoinRequest::query()->where('user_id', $user->id)->delete();
        //            }
        //        }
    }


    public function saved(User $user)
    {
        if (!$user->enableSaving) return;

        //        if (!$user->agency_id) {
        //            AgencyJoinRequest::query()->where('user_id', $user->id)->delete();
        //        }
        //        if ($user->agency_id) {
        //            $agency = Agency::query()->find($user->agency_id);
        //            if (!$agency) {
        //                AgencyJoinRequest::query()->where('user_id', $user->id)->delete();
        //            }
        //        }
    }

    /**
     * Handle the User "deleted" event.
     *
     * @param \App\Models\User $user
     * @return void
     */
    public function deleted(User $user)
    {


        // $user->profile()->delete();
        // AgencyJoinRequest::query()->where('user_id', $user->id)->delete();
        // $user->ownerRoom()->delete();
        // //        $user->carousels()->delete();
        // //        Agency::where('owner_id',$user->id)->delete();
        // FamilyUser::where('user_id',$user->id)->delete();
        // Pack::where('user_id',$user->id)->delete();
        // Follow::where('user_id', $user->id)
        //       ->orWhere('followed_user_id', $user->id)
        //       ->delete();
    }



    public function deleting(User $user)
    {
        // if ($user && UserHandling::checkIfUserOwnerOfAgency($user)){
        //     throw ValidationException::withMessages(['error' => __('This User is the host Of agency can\'t delete it')]);
        // }
        // $user->email = null;
        // $user->phone = null;
        // $user->google_id = null;
        // $user->huawei_id = null;
        // $user->save();
        // Report_user::query()->where('user_id', $user->id)->orWhere('Reporter_id', $user->id)->delete();


    }


    /**
     * Handle the User "restored" event.
     *
     * @param \App\Models\User $user
     * @return void
     */
    public function restored(User $user)
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     *
     * @param \App\Models\User $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
        $user->profile()->delete();
        AgencyJoinRequest::query()->where('user_id', $user->id)->delete();
        $user->ownerRoom()->delete();
        //        $user->carousels()->delete();
        //        Agency::where('owner_id',$user->id)->delete();
        FamilyUser::where('user_id',$user->id)->delete();
        Pack::where('user_id',$user->id)->delete();
        Follow::where('user_id', $user->id)
              ->orWhere('followed_user_id', $user->id)
              ->delete();
        BlackList::where('user_id',$user->id)->orWhere('from_uid',$user->id)->delete();
    }

    /**
     * @param User $user
     * @return void
     */
    public function creating(User $user)
    {
        $user->uuid = (string)rand(1000000, 9999999);
    }


    /**
     * @param User $user
     * @param $target
     * @param $month_received
     * @param $hours
     * @param $days
     * @param $t
     * @param $ap
     * @return void
     */
    private function updateOrCreateAgentsAndSalary(User $user, $target, $month_received, $hours, $days, $t, $ap ): void
    {
        UserTarget::query()->updateOrCreate(
            [
                'user_id'   => $user->id,
                'add_month' => Carbon::now()->month,
                'add_year'  => Carbon::now()->year
            ],
            [
                'user_id'             => $user->id,
                'add_month'           => Carbon::now()->month,
                'add_year'            => Carbon::now()->year,
                'agency_id'           => $user->agency_id,
                'target_id'           => $target->id,
                'target_diamonds'     => $target->diamonds,
                'target_usd'          => $target->usd,
                'target_hours'        => $target->hours,
                'target_days'         => $target->days,
                'target_agency_share' => $target->agency_share,
                'user_diamonds'       => $month_received,
                'user_hours'          => $hours,
                'user_days'           => $days,
                'user_obtain'         => $t,
                'agency_obtain'       => $t * $ap
            ]
        );
        $this->updateSalaries($user, $t, $ap, $hours, $target, $days, $month_received, );
    }


    /**
     * @param User $user
     * @param $t
     * @param $ap
     * @param $hours
     * @param $target
     * @param $days
     * @return void
     */
    private function updateSalaries(User &$user, $t, $ap, $hours, $target, $days, $month_received  ): void
    {
        $values = [
            'user_id'             => $user->id,
            'add_month'           => Carbon::now()->month,
            'add_year'            => Carbon::now()->year,
            'agency_id'           => $user->agency_id,
            'family_id'           => $user->family_id,
            'target_id'           => $target->id,
            'target_diamonds'     => $target->diamonds,
            'target_usd'          => $target->usd,
            'target_hours'        => $target->hours,
            'target_days'         => $target->days,
            'target_agency_share' => $target->agency_share,
            'user_diamonds'       => $month_received,
            'user_hours'          => $hours,
            'user_days'           => $days,

        ];
        if (0.0 < $t){
            $values['user_obtain'] = $t;
            $values['agency_obtain'] = $t * $ap;
        }
        UserTarget::query ()->updateOrCreate (
            [
                'user_id'=>$user->id,
                'add_month'=>Carbon::now ()->month,
                'add_year'=>Carbon::now ()->year
            ],
            $values
        );

        $values = [
            'user_id'        => $user->id,
            'month'          => Carbon::now()->month,
            'year'           => Carbon::now()->year,
            'agency_sallary' => $t * $ap,
            'user_agency_id' => $user->agency_id,
            'hours'          => "$hours / $target->hours",
            'days'           => "$days / $target->days"
        ];
        if (0 < $t){
            $values['sallary'] = $t;
        }
        UserSallary::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'month'   => Carbon::now()->month,
                'year'    => Carbon::now()->year
            ],
            $values
        );

    }


    public function saving(User $user)
    {
        if (isset($user->oldDiValue)) {
            unset($user->oldDiValue);
            unset($user->oldDiamoundValue);
        }
        if (!$user->enableSaving) return;

        try {
            if ($user->agency_id) {
                if ($user->is_host == 0) {
                    $user->coins = 0;
                    $user->monthly_diamond_received = 0;

                }
                $user->is_host = 1;
            }
            if ($user->agency_id == 0 || $user->agency_id == null) {
                if ($user->is_host == 1) {
                    //                    UserHandling::kickUserFromAgency($user);
                    $user->coins = 0;
                    //                    $user->monthly_diamond_received = 0;
                }
                $user->is_host = 0;
            }
            if ($user->status == 0) {
                $user->tokens()->delete();
            }

            if ($user->isDirty('uuid') && !$user->uuid) {
                do {
                    // توليد قيمة uuid عشوائية
                    $uuid = (string)rand(1000000, 9999999);
                    $check_users = User::where("uuid", $uuid)->exists();
                    $check_wares = Ware::where("value", $uuid)->exists();

                } while ($check_users || $check_wares);

                $user->uuid = $uuid;

            }


            $month_received = $user->monthly_diamond_received;



        } catch (\Illuminate\Database\QueryException $e) {

        }

    }

    public function calculateUsdFromTarget(Target $target, mixed $hours, mixed $days): int|float
    {
        $per = 0.50;
        if ($target->hours <= $hours) {
            $per += 0.20;
        }
        if ($target->days <= $days) {
            $per += 0.30;
        }

        return $target->usd * $per;
    }

    /**
     * @param User $user
     * @return void
     */

}
