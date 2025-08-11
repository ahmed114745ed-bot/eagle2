<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CleanGiftLogsJob  implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
{

    User::select('id', 'monthly_diamond_received', 'agency_id')->where('agency_id','!=' ,0)
    ->chunk(500, function ($users) {
        foreach ($users as $user) {
            $monthlyLimit = $user->monthly_diamond_received;

            // جلب سجلات الهدايا بجميع الأنواع وترتيبها حسب تاريخ الإنشاء (قد تختار asc أو desc حسب المنطق)
            $giftLogs = DB::table('gift_logs as gl')
                ->join('gifts as g', 'gl.giftId', '=', 'g.id')
                ->where('gl.receiver_id', $user->id)
                ->where('gl.created_at', '>=', '2025-07-31 21:00:00')
                ->where('gl.created_at', '<', '2025-08-30 21:00:00')
                ->orderBy('gl.created_at', 'asc')  // الأقدم أولاً للحذف أو التعديل
                ->select('gl.id', 'gl.giftPrice', 'g.type as gift_type')
                ->get();

            $total = $giftLogs->sum('giftPrice');

            if ($total <= $monthlyLimit) {
                // الرصيد مساوي أو أكبر، لا حاجة لحذف
                Log::info("No deletion needed for user {$user->id}, total gifts {$total} within limit {$monthlyLimit}");
                continue;
            }

            // حذف سجلات النوع 6 أولا حتى ينخفض المجموع
            $remainingToRemove = $total - $monthlyLimit;

            // سجلات النوع 6 فقط
            $type6Logs = $giftLogs->where('gift_type', 6);

            foreach ($type6Logs as $log) {
                if ($remainingToRemove <= 0) break;

                if ($log->giftPrice <= $remainingToRemove) {
                    // حذف كامل للسجل
                    DB::table('gift_logs')->where('id', $log->id)->delete();
                    $remainingToRemove -= $log->giftPrice;
                } else {
                    // تعديل جزئي للسجل (تخفيض giftPrice)
                    $newPrice = $log->giftPrice - $remainingToRemove;
                    DB::table('gift_logs')->where('id', $log->id)->update(['giftPrice' => $newPrice]);
                    $remainingToRemove = 0;
                }
            }

            if ($remainingToRemove > 0) {
                // باقي الحذف من الأنواع الأخرى
                $otherLogs = $giftLogs->where('gift_type', '!=', 6);

                foreach ($otherLogs as $log) {
                    if ($remainingToRemove <= 0) break;

                    if ($log->giftPrice <= $remainingToRemove) {
                        // حذف كامل للسجل
                        DB::table('gift_logs')->where('id', $log->id)->delete();
                        $remainingToRemove -= $log->giftPrice;
                    } else {
                        // تعديل جزئي للسجل (تخفيض giftPrice)
                        $newPrice = $log->giftPrice - $remainingToRemove;
                        DB::table('gift_logs')->where('id', $log->id)->update(['giftPrice' => $newPrice]);
                        $remainingToRemove = 0;
                    }
                }
            }

            Log::info("Cleanup for user {$user->id} done, reduced by " . ($total - $monthlyLimit));
        }
    });
}

    
    
}
