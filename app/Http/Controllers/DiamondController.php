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
use Utd\Achievements\Entities\MonthlyDiamondReceive;
use Modules\FixedTarget\Services\FixedTargetService;
use Modules\FixedTarget\Services\FixedTargetV2Service;
use App\Support\PackageHelper;

class DiamondController extends Controller
{


    public function calculateMonthlyDiamondReceived()
    {
        $timezone = getTimezone();
        $startOfMonth = \Carbon\Carbon::now($timezone)->startOfMonth()->copy()->setTimezone('UTC');

        $users = DB::table('users')
            ->select('id', 'agency_id')
            ->whereNotNull('agency_id')
            ->where('agency_id', '>', 0)
            ->whereIn('type_user', [1, 2])
            ->get();

        foreach ($users as $user) {
            $join = DB::table('users_joined_agencies')
                ->where('user_id', $user->id)
                ->where('agency_id', $user->agency_id)
                ->orderByDesc('join_date')
                ->first();

            $startDate = $startOfMonth;
            if ($join && Carbon::parse($join->join_date, $timezone)->greaterThan($startOfMonth)) {
                $startDate = Carbon::parse($join->join_date, $timezone);
            }

            $totalReceived = DB::table('gift_logs')
                ->where('receiver_id', $user->id)
                ->where('created_at', '>=', $startDate)
                ->where('agency_id', $user->agency_id)
                ->selectRaw('SUM(giftPrice) as total')
                ->value('total');

            // تحديث جدول users

            $monthlyDiamond = $totalReceived ?? 0;
            if (PackageHelper::isInstalled('achievement')) {
                uploadMonthlyDiamondReceive($user->id, $monthlyDiamond);
            }
        }

        // $totalReceived = DB::table('gift_logs')
        //     ->where('receiver_id', $user->id)
        //     ->where('created_at', '>=', $startDate)
        //     ->where('agency_id', $user->agency_id)
        //     ->selectRaw('SUM(giftPrice) as total')
        //     ->value('total');
        // $totalDiamond = $totalReceived ?? 0;
        // uploadMonthlyDiamondReceive($user->id, $totalDiamond);
         return response()->json([
            'status' => true,
            'message' => 'تم تحديث الماس الشهري لجميع المستخدمين (type_user = 0).'
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
