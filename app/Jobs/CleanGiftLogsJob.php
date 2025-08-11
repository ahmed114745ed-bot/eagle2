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
    
            $allGiftLogs = DB::table('gift_logs')
                ->join('gifts', 'gift_logs.giftId', '=', 'gifts.id')
                ->where('gift_logs.receiver_id', $user->id)
                ->whereMonth('gift_logs.created_at', now()->month)
                ->whereYear('gift_logs.created_at', now()->year)
                ->orderBy('gift_logs.created_at', 'asc')
                ->select('gift_logs.*', 'gifts.type as gift_type')
                ->get();
    
            $total = 0;
            $keepIdsType6 = [];
            $partialUpdateId = null;
            $partialNewValue = null;
    
            foreach ($allGiftLogs as $log) {
                if ($total < $monthlyReceived) {
                    if ($total + $log->giftPrice <= $monthlyReceived) {
                        $total += $log->giftPrice;
                        if ($log->gift_type == 6) {
                            $keepIdsType6[] = $log->id;
                        }
                    } else {
                        $needed = $monthlyReceived - $total;
                        $total += $needed;
                        if ($log->gift_type == 6) {
                            $partialUpdateId = $log->id;
                            $partialNewValue = $needed;
                            $keepIdsType6[] = $log->id;
                        }
                        break;
                    }
                } else {
                    break;
                }
            }
    
            if ($partialUpdateId && $partialNewValue !== null) {
                DB::table('gift_logs')
                    ->where('id', $partialUpdateId)
                    ->update(['giftPrice' => $partialNewValue]);
            }
    
            $deleted = DB::table('gift_logs')
                ->join('gifts', 'gift_logs.giftId', '=', 'gifts.id')
                ->where('gift_logs.receiver_id', $user->id)
                ->where('gifts.type', 6)
                ->whereMonth('gift_logs.created_at', now()->month)
                ->whereYear('gift_logs.created_at', now()->year)
                ->whereNotIn('gift_logs.id', $keepIdsType6)
                ->delete();
        }
    }
    
}
