<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TestDiamondController extends Controller
{
public function discrepancyView(Request $request)
{
    // الشهر والسنة المطلوبين أو الحالي
    $month = $request->query('month', Carbon::now()->month);
    $year = $request->query('year', Carbon::now()->year);

    // جلب كل المستخدمين الذين لديهم أي سجل في الشهر المحدد
    $usersMonthly = DB::table('monthly_diamond_receives')
        ->where('month', $month)
        ->where('year', $year)
        ->get();

    $results = [];

    foreach ($usersMonthly as $monthly) {
        $userId = $monthly->user_id;

        // جمع عدد الماسات فعلياً من gift_logs: عدد الهدايا * السعر
        $totalGifts = DB::table('gift_logs')
            ->where('receiver_id', $userId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->selectRaw('SUM(giftNum * giftPrice) as total')
            ->value('total') ?? 0; // fallback للصفر إذا لا يوجد هدايا

        // المقارنة بعد تحويل القيم إلى float
        $isDifferent = floatval($totalGifts) != floatval($monthly->monthly_diamond_received);
        if ($isDifferent) {
            $results[] = [
                'user_id' => $userId,
                'registered' => floatval($monthly->monthly_diamond_received),
                'actual' => floatval($totalGifts),
            ];
        }
    }

    return view('discrepancy', [
        'results' => $results,
        'month' => $month,
        'year' => $year,
    ]);
}

}
