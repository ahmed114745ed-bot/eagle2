<?php

namespace App\Http\Controllers;

use App\Helpers\Common;
use App\Models\User;
use Illuminate\Http\Request;
use App\Facades\UserHandling;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\FixedTarget\Services\FixedTargetService;
use Modules\FixedTarget\Services\FixedTargetV2Service;

class DiamondController extends Controller
{


public function calculateMonthlyDiamondReceived()
{
    $timezone = Common::timeZone();
    $now = Carbon::now($timezone);
    $startOfMonth = $now->copy()->startOfMonth();

    $users = DB::table('users')
        ->select('id', 'agency_id')
        ->whereNotNull('agency_id')
        ->where('agency_id', '>', 0)
        ->whereIn('type_user', [1,2])
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
        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'monthly_diamond_received' => $totalReceived ?? 0
            ]);
    }

    return response()->json([
        'status' => true,
        'message' => 'تم تحديث الماس الشهري لجميع المستخدمين (type_user = 0).'
    ]);
}

public function calculateSalary()
{
    $month =request()->month ?? now()->month;
    $year =request()->year ?? now()->year;
     User::query()
            ->where('agency_id', '!=', 0)
            ->where('salary_is_updated', 1)
            ->where('type_user', '!=', 0)
            ->chunk(500, function ($users) use($month, $year){
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
    $month =request()->month ?? now()->month;
    $year =request()->year ?? now()->year;
    Log::info('user',['test'=>$year ]);

     User::query()
            ->where('agency_id', '!=', 0)
            // ->where('salary_is_updated', 1)
            ->where('type_user', '!=', 0)
            ->chunk(500, function ($users) use($month, $year){
                foreach ($users as $user) {
                    try {
                        Log::info('user',['test'=>$user->id ]);

                        $targetService = new FixedTargetV2Service($user, month: $month, year: $year);
                        $targetService->calculateTarget();
                    } catch (\Throwable $e) {
                        dd($e->getMessage());
                        // $this->error("Failed user ID {$user->id}");
                    }
                }
            });

    return response()->json([
        'status' => true,
        'message' => 'تم تحديث الماس الشهري لجميع المستخدمين (type_user = 0).'
    ]);
}

}



