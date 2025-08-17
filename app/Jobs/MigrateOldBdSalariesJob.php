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

        \Log::info("start    MigrateOldBdSalariesJob ...........");

        DB::beginTransaction();

        try {
            foreach ($bdSalaries as $bdSalary) {
                $bdAppId = $bdSalary->bd_id;
                $bdId    = self::getBdUserId($bdAppId);
                $agencyId = $bdSalary->agency_id;
                $month    = $bdSalary->month;
                $year     = $bdSalary->year;
    
                if ($bdId == 51) {
                    \Log::info("Migrating salary record", [
                        'bd_app_id' => $bdAppId,
                        'bd_id'     => $bdId,
                        'agency_id' => $agencyId,
                        'month'     => $month,
                        'year'      => $year,
                    ]);
                }
                // 1) حفظ الرواتب الخاصة بالمستخدمين (زي ما هو)
                $userSalaries = UserSallary::where('user_agency_id', $agencyId)
                    ->where('month', $month)
                    ->where('year', $year)
                    ->get();
    
                foreach ($userSalaries as $userSallary) {
                    BdAgencyHostSallary::updateOrCreate(
                        [
                            'bd_id'     => $bdId,
                            'agency_id' => $agencyId,
                            'user_id'   => $userSallary->user_id,
                            'month'     => $month,
                            'year'      => $year,
                        ],
                        [
                            'amount'        => $userSallary?->dB ?? 0,
                            'user_sallary'  => $userSallary->sallary,
                            'agency_sallary'=> $userSallary->agency_sallary,
                            'bd_user_id'    => $bdAppId,
                            'created_at'    => $userSallary->created_at,
                        ]
                    );
                }
    
                if ($bdId == 51) {
                    \Log::info("Updated BdAgencyHostSallary", [
                        'user_id' => $userSallary->user_id,
                        'user_sallary' => $userSallary->sallary,
                    ]);
                }
                // 2) اجمع كل الرواتب لنفس الـ bd_id + الشهر + السنة
                $totals = BDSallary::where('bd_id', $bdAppId)
                    ->where('month', $month)
                    ->where('year', $year)
                    ->selectRaw('SUM(sallary) as total_salary, SUM(cut_amount) as total_cut')
                    ->first();
    
                // 3) استبدال مباشر (بدون مضاعفة)
                BdSalary::updateOrCreate(
                    [
                        'bd_id' => $bdId,
                        'month' => $month,
                        'year'  => $year,
                    ],
                    [
                        'salary'     => $totals->total_salary ?? 0,
                        'cut_amount' => $totals->total_cut ?? 0,
                    ]
                );

                if ($bdId == 51) {
                    \Log::info("Updated BdSalary totals", [
                        'total_salary' => $totals->total_salary ?? 0,
                        'total_cut'    => $totals->total_cut ?? 0,
                    ]);
                }
            }
    
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            logger()->error('MigrateBdSalariesJob error: ' . $e->getMessage());
        }
    }

    protected static function getBdUserId($bdAppId)
    {
        return Bd::where('app_id', $bdAppId)->value('id') ?? 0;
    }
}
