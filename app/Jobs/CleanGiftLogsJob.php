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
        User::select('id', 'monthly_diamond_received', 'agency_id')
            ->chunk(500, function ($users) {
                foreach ($users as $user) {
                    $monthlyReceived = $user->monthly_diamond_received;

                    $total = 0;
                    $keepIdsType6 = [];
                    $partialUpdateId = null;
                    $partialNewValue = null;

                    DB::table('gift_logs as gl')
                        ->join('gifts as g', 'gl.giftId', '=', 'g.id')
                        ->join('users as u', 'gl.receiver_id', '=', 'u.id')
                        ->where('gl.receiver_id', $user->id)
                        ->where('u.agency_id', $user->agency_id)
                        ->whereBetween('gl.created_at', ['2025-07-31 21:00:00', '2025-08-30 21:00:00'])
                        ->orderBy('gl.id', 'asc')
                        ->select('gl.id as gl_id', 'gl.*', 'g.type as gift_type')
                        ->chunkById(500, function ($logs) use ($monthlyReceived, &$total, &$keepIdsType6, &$partialUpdateId, &$partialNewValue) {
                            foreach ($logs as $log) {
                                if ($total < $monthlyReceived) {
                                    if ($total + $log->giftPrice <= $monthlyReceived) {
                                        $total += $log->giftPrice;
                                        if ($log->gift_type == 6) {
                                            $keepIdsType6[] = $log->gl_id;
                                        }
                                    } else {
                                        $needed = $monthlyReceived - $total;
                                        $total += $needed;
                                        if ($log->gift_type == 6) {
                                            $partialUpdateId = $log->gl_id;
                                            $partialNewValue = $needed;
                                            $keepIdsType6[] = $log->gl_id;
                                        }
                                        return false; // توقف بعد الوصول للرصد المطلوب
                                    }
                                } else {
                                    return false; // توقف إذا اكتفينا
                                }
                            }
                            return true; // أكمل إذا لم نصل بعد للحد
                        }, 'gl_id');

                    // تعديل السجل الجزئي
                    if ($partialUpdateId && $partialNewValue !== null) {
                        DB::table('gift_logs')
                            ->where('id', $partialUpdateId)
                            ->update(['giftPrice' => $partialNewValue]);
                    }

                    // حذف السجلات الزائدة
                    $deleted = DB::table('gift_logs as gl')
                        ->join('gifts as g', 'gl.giftId', '=', 'g.id')
                        ->join('users as u', 'gl.receiver_id', '=', 'u.id')
                        ->where('gl.receiver_id', $user->id)
                        ->where('u.agency_id', $user->agency_id)
                        ->where('g.type', 6)
                        ->whereBetween('gl.created_at', ['2025-07-31 21:00:00', '2025-08-30 21:00:00'])
                        ->whereNotIn('gl.id', $keepIdsType6)
                        ->delete();

                    Log::info("Gift logs cleanup for user {$user->id}", [
                        'monthly_received' => $monthlyReceived,
                        'total_kept'       => $total,
                        'records_deleted'  => $deleted
                    ]);
                }
            });
    }

    
    
}
