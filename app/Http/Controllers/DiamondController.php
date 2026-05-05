<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Helpers\Common;
use Illuminate\Http\Request;
use App\Facades\UserHandling;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Jobs\CalculateUserTargetJob;
use Illuminate\Support\Facades\Auth;
use App\Models\MonthlyDiamondReceive;
use Modules\FixedTarget\Services\FixedTargetService;
use Modules\FixedTarget\Services\FixedTargetV2Service;

class DiamondController extends Controller
{


    public function calculateMonthlyDiamondReceived()
    {
        $timezone = getTimezone();
        $date = Carbon::now($timezone);
        $currentMonth = $date->month;
        $currentYear = $date->year;
        $startOfMonth = Carbon::now($timezone)->startOfMonth()->copy()->setTimezone('UTC');

        $processedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;

        DB::table('users')
            ->select('id', 'agency_id')
            ->whereNotNull('agency_id')
            ->where('agency_id', '>', 0)
            ->whereIn('type_user', [1, 2])
            ->orderBy('id')
            ->chunk(500, function ($users) use ($timezone, $startOfMonth, $currentMonth, $currentYear, &$processedCount, &$updatedCount, &$skippedCount) {
                foreach ($users as $user) {
                    try {
                        $join = DB::table('users_joined_agencies')
                            ->where('user_id', $user->id)
                            ->where('agency_id', $user->agency_id)
                            ->orderByDesc('join_date')
                            ->first();

                        $startDate = $startOfMonth;
                        if ($join && Carbon::parse($join->join_date, $timezone)->greaterThan($startOfMonth)) {
                            $startDate = Carbon::parse($join->join_date, $timezone)->setTimezone('UTC');
                        }

                        $totalReceived = DB::table('gift_logs')
                            ->where('receiver_id', $user->id)
                            ->where('created_at', '>=', $startDate)
                            ->where('agency_id', $user->agency_id)
                            ->selectRaw('SUM(giftPrice) as total')
                            ->value('total');

                        $newMonthlyDiamond = $totalReceived ?? 0;

                        $currentRecord = MonthlyDiamondReceive::where('user_id', $user->id)
                            ->where('month', $currentMonth)
                            ->where('year', $currentYear)
                            ->first();

                        $oldMonthlyDiamond = $currentRecord ? $currentRecord->monthly_diamond_received : 0;

                        if ($newMonthlyDiamond != $oldMonthlyDiamond) {
                            uploadMonthlyDiamondReceive($user->id, $newMonthlyDiamond);

                            DB::table('users')
                                ->where('id', $user->id)
                                ->update([
                                    'salary_is_updated' => 1,
                                ]);

                            $updatedCount++;
                        } else {
                            $skippedCount++;
                        }

                        $processedCount++;
                    } catch (\Throwable $e) {
                        Log::error("Failed to calculate monthly diamond for user {$user->id}: " . $e->getMessage());
                    }
                }
            });

        return response()->json([
            'status' => true,
            'message' => "تم معالجة {$processedCount} مستخدم - تم التحديث: {$updatedCount} - تم تخطيهم: {$skippedCount}",
            'processed_count' => $processedCount,
            'updated_count' => $updatedCount,
            'skipped_count' => $skippedCount
        ]);
    }

    public function calculateSalary()
    {
        $month = request()->month ?? now()->month;
        $year = request()->year ?? now()->year;
        User::query()
            ->where('agency_id', '!=', 0)
            ->where('salary_is_updated', 1)
            ->where('type_user', '!=', 0)
            ->chunk(500, function ($users) use ($month, $year) {
                foreach ($users as $user) {
                    try {
                        $targetService = new FixedTargetV2Service($user, month: $month, year: $year);
                        $targetService->calculateTarget();
                    } catch (\Throwable $e) {

                        $this->error("Failed user ID {$user->id}");
                    }
                }
            });

        return response()->json([
            'status' => true,
            'message' => 'تم تحديث الماس الشهري لجميع المستخدمين (type_user = 0).'
        ]);
    }

    public function calculateSalaryV2()
    {
        $month = request()->month ?? now()->month;
        $year = request()->year ?? now()->year;

        User::query()
            ->where('agency_id', '!=', 0)
            // ->where('salary_is_updated', 1)
            ->where('type_user', '!=', 0)
            ->chunk(500, function ($users) use ($month, $year) {
                foreach ($users as $user) {
                    try {
                        CalculateUserTargetJob::dispatch($user, $month, $year)->onQueue('calculate-target');
                    } catch (\Throwable $e) {
                        dd($e->getMessage());
                    }
                }
            });

        return response()->json([
            'status' => true,
            'message' => 'تم تحديث الماس الشهري لجميع المستخدمين (type_user = 0).'
        ]);
    }


    public function copyMonthlyDiamondReceive()
    {
        User::chunk(100, function ($users) {
            foreach ($users as $user) {
                MonthlyDiamondReceive::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'month'   => now()->month,
                        'year'    => now()->year,
                    ],
                    [
                        'monthly_diamond_received' => $user->monthly_diamond_received,
                        'old_diamond' => $user->monthly_diamond_received,
                    ]
                );
            }
        });
        return 'done!';
    }
}
