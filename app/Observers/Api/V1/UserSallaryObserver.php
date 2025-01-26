<?php

namespace App\Observers\Api\V1;

use App\Models\Agency;
use App\Models\UserSallary;
use App\Models\AgencySallary;
use App\Classes\Enums\NotificationType;
use App\Jobs\SendCustomOfficialMessageToUser;

class UserSallaryObserver
{

    public function created(UserSallary $userSalary)
    {

    }


    public function updated(UserSallary $userSalary)
    {

        $this->updateOrCreateAgencySallary($userSalary);
    }


    public function deleted(UserSallary $userSalary)
    {

    }


    public function restored(UserSallary $userSalary)
    {
        //
    }


    public function forceDeleted(UserSallary $userSalary)
    {
        //
    }
    public function saved(UserSallary $userSalary)
    {
        $this->updateOrCreateAgencySallary($userSalary, false);
    }

    public function creating(UserSallary $userSalary)
    {

        if(!$userSalary->extras) $userSalary->extras = '';
        $this->updateOrCreateAgencySallary($userSalary, true);
    }


    public function updating(UserSallary $userSalary)
    {
        if(!$userSalary->extras) $userSalary->extras = '';

        if ($userSalary->isDirty('sallary') && $userSalary->sallary > 0){
            dispatch(new SendCustomOfficialMessageToUser($userSalary->user_id, NotificationType::TARGET))->onQueue('notification');
        }
    }


    public function saving(UserSallary $userSalary)
    {

    }

    /**
     * @param UserSallary $userSalary
     * @return void
     */
    public function updateOrCreateAgencySallary(UserSallary $userSalary, bool $isCreate = false): void
    {

        $agency = Agency::find($userSalary->user_agency_id);
        if ($userSalary->user_agency_id != 0 /*&& ($userSalary->isDirty('agency_sallary') || $isCreate)*/ && $agency && $agency->status == 1) {
            $agency_id    = $userSalary->user_agency_id;
            $month        = 0;
            $year         = 0;
            $period_id         = $userSalary->period_id;
            $agencySalary =
                AgencySallary::query()->where('month', $month)->where('year', $year)->where('period_id', $period_id)->where('agency_id', $agency_id)->first();


            $salary = UserSallary::where('user_agency_id', $agency_id)->where('period_id', $period_id)->sum('agency_sallary');

            if ($agencySalary) {
                $agencySalary->update([
                                          'sallary' => $salary
                                      ]);
            } else {
                AgencySallary::query()->create([
                                                   'sallary' => $salary, 'agency_id' => $agency_id, 'month' => '0',
                                                   'year'    => '0', 'period_id' => $period_id
                                               ]);
            }
        }
    }
}
