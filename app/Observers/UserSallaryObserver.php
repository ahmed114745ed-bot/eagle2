<?php

namespace App\Observers;

use App\Models\Agency;
use App\Models\BDSallary;
use App\Models\UserSallary;
use App\Models\AgencySallary;
use App\Classes\Enums\NotificationType;
use App\Jobs\SendCustomOfficialMessageToUser;

class UserSallaryObserver
{
    public function updated(UserSallary $userSalary)
    {
        $this->updateOrCreateAgencySallary($userSalary);
        $this->updateOrCreateBDSallary($userSalary);
    }

    public function saved(UserSallary $userSalary)
    {
        $this->updateOrCreateAgencySallary($userSalary);
        $this->updateOrCreateBDSallary($userSalary);
    }

    public function creating(UserSallary $userSalary)
    {
        if (!$userSalary->extras) $userSalary->extras = '';
        $this->updateOrCreateAgencySallary($userSalary);
        $this->updateOrCreateBDSallary($userSalary);
    }

    public function updating(UserSallary $userSalary)
    {
        if (!$userSalary->extras) $userSalary->extras = '';

        if ($userSalary->isDirty('sallary') && $userSalary->sallary > 0) {
            dispatch(new SendCustomOfficialMessageToUser($userSalary->user_id, NotificationType::TARGET))->onQueue('notification');
        }
    }

    public function updateOrCreateAgencySallary(UserSallary $userSalary): void
    {
        $app_feature = \Cache::get('host_agency');
        if ($app_feature) {
            $agency = Agency::find($userSalary->user_agency_id);
            if ($userSalary->user_agency_id != 0 && $agency && $agency->status == 1) {
                $agency_id    = $userSalary->user_agency_id;
                $month        = now()->month;
                $year         = now()->year;
                $agencySalary = AgencySallary::query()
                    ->where('month', $month)
                    ->where('year', $year)
                    ->where('agency_id', $agency_id)
                    ->first();

                $salary = UserSallary::query()
                    ->where('user_agency_id', $agency_id)
                    ->where('month', now()->month)
                    ->where('year', now()->year)
                    ->sum('agency_sallary');

                if ($agencySalary) {
                    $agencySalary->update(['sallary' => $salary]);
                } else {
                    AgencySallary::query()->create([
                        'sallary' => $salary,
                        'agency_id' => $agency_id,
                        'month' => $month,
                        'year'    => $year
                    ]);
                }
            }
        }
    }

    public function updateOrCreateBDSallary(UserSallary $userSalary): void
    {
        $agency = Agency::find($userSalary->user_agency_id);

        if ($userSalary->user_agency_id != 0 && $agency && $agency->bd_id && $agency->status == 1) {
            $bdId    = $agency->bd_id;
            $month   = now()->month;
            $year    = now()->year;
            $agencyId = $agency->id;

            $totals = UserSallary::query()
                ->where('user_agency_id', $agency->id)
                ->where('month', now()->month)
                ->where('year', now()->year)
                ->selectRaw('SUM(agency_sallary) as total_agency_sallary, SUM(sallary) as total_users_sallary, SUM(diamond) as total_diamond')
                ->first();

            $totalBdSallary = UserSallary::query()
                ->where('user_agency_id', $agencyId)
                ->where('month', $month)
                ->where('year', $year)
                ->sum('dB');

            $bdSalary = BDSallary::query()
                ->where([
                    'bd_id'     => $bdId,
                    'agency_id' => $agencyId,
                    'month'     => $month,
                    'year'      => $year,
                ])
                ->lock()
                ->first();

            if ($bdSalary) {
                $bdSalary->update([
                    'sallary' => $totalBdSallary,
                    'total_agency_sallary' => $totals->total_agency_sallary ?? 0,
                    'total_users_sallary' =>  $totals->total_users_sallary ?? 0,
                    'total_diamond' =>  $totals->total_diamond ?? 0,
                ]);
            } else {
                BDSallary::query()->create([
                    'bd_id'      => $bdId,
                    'agency_id'  => $agencyId,
                    'month'      => $month,
                    'year'       => $year,
                    'cut_amount' => 0,
                    'sallary'    => $totalBdSallary,
                    'is_paid'    => false,
                    'total_agency_sallary' => $totals->total_agency_sallary ?? 0,
                    'total_users_sallary' =>  $totals->total_users_sallary ?? 0,
                    'total_diamond' =>  $totals->total_diamond ?? 0,
                ]);
            }
        }
    }
}
