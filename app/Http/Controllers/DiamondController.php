<?php

namespace App\Http\Controllers;

use App\Helpers\Common;
use App\Models\User;
use Illuminate\Http\Request;
use App\Facades\UserHandling;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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
        ->where('type_user', 1) 
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
}
