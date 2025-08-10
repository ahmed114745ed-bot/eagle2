<?php

namespace App\Jobs;


use App\Models\Bd;
use App\Models\BDSallary;
use App\Models\BdAgencyHostSallary;
use App\Models\BdSalary;
use App\Models\UserSallary;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;

class MigrateOldBdSalariesJob implements ShouldQueue
{
    use Dispatchable,InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $bdSalaries = BDSallary::all();

        DB::beginTransaction();

        try {
            foreach ($bdSalaries as $bdSalary) {
                $bdAppId = $bdSalary->bd_id;
                $bdId = self::getBdUserId($bdAppId);
                $agencyId = $bdSalary->agency_id;
                $month = $bdSalary->month;
                $year = $bdSalary->year;

                $userSalaries = UserSallary::where('user_agency_id', $agencyId)
                    ->where('month', $month)
                    ->where('year', $year)
                    ->get();

                foreach ($userSalaries as $userSallary) {
                    BdAgencyHostSallary::updateOrCreate(
                        [
                            'bd_id' => $bdId,
                            'agency_id' => $agencyId,
                            'user_id' => $userSallary->user_id,
                            'month' => $month,
                            'year' => $year,
                        ],
                        [
                            'amount' => $userSallary->dB,
                            'user_sallary' => $userSallary->sallary,
                            'agency_sallary' => $userSallary->agency_sallary,
                            'bd_user_id' => $bdAppId,
                            'created_at'     => $userSallary->created_at,
                        ]
                    );
                }

                BdSalary::updateOrCreate(
                    [
                        'bd_id' => $bdId,
                        'month' => $month,
                        'year' => $year,
                    ],
                    [
                        'salary' => $bdSalary->sallary,
                        'cut_amount' => $bdSalary->cut_amount,
                    ]
                );
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            logger()->error('MigrateBdSalariesJob error: ' . $e->getMessage());
            throw $e;

        }
    }

    protected static function getBdUserId($bdAppId)
    {
        return Bd::where('app_id', $bdAppId)->value('id') ?? 0;
    }
}
