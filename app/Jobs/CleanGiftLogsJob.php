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
        $users = User::select('id', 'monthly_diamond_received')->get();

        foreach ($users as $user) {
            $monthlyReceived = $user->monthly_diamond_received;
        
            $giftLogs = DB::table('gift_logs')
            ->join('gifts', 'gift_logs.giftId', '=', 'gifts.id')
            ->where('gift_logs.receiver_id', $user->id)
            ->where('gifts.type', 6)
            ->whereMonth('gift_logs.created_at', now()->month)
            ->whereYear('gift_logs.created_at', now()->year)
            ->orderBy('gift_logs.created_at', 'asc')
            ->select('gift_logs.*') 
            ->get();
        
            $total = 0;
            $keepIds = [];
            $partialUpdateId = null;
            $partialNewValue = null;
        
            foreach ($giftLogs as $log) {
                if ($total + $log->giftPrice < $monthlyReceived) {
                    $keepIds[] = $log->id;
                    $total += $log->giftPrice;
                } elseif ($total < $monthlyReceived) {
                    // نأخذ جزء من هذا السجل
                    $needed = $monthlyReceived - $total;
                    $partialUpdateId = $log->id;
                    $partialNewValue = $needed;
                    $total += $needed;
                    $keepIds[] = $log->id;
                    break;
                } else {
                    break;
                }
            }
        
            // تحديث السجل الجزئي
            if ($partialUpdateId && $partialNewValue !== null) {
                DB::table('gift_logs')
                    ->where('id', $partialUpdateId)
                    ->update(['giftPrice' => $partialNewValue]);
            }
        
            // حذف الباقي
            $deleted = DB::table('gift_logs')
                ->where('receiver_id', $user->id)
                ->where('type', 6)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->whereNotIn('id', $keepIds)
                ->delete();
        
   
        }
        
    }
}
