<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\GiftLog;
use App\Models\Gift;
class GiftUpdateUsedCountWeakly extends Command
{

    protected $signature = 'update-gift-weakly:cron';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        Gift::query()->update(['use_count' => 0]);
        $currentDateTime = \Carbon\Carbon::now();
        $oneMonthFromNow = $currentDateTime->subMonth();
        $gift_logs = GiftLog::query()->with("gift")->select(['giftId', DB::raw('sum(giftNum) as total')])
                    ->whereDate("created_at","<=",date("Y-m-d"))
                    ->whereDate("created_at",">=",$oneMonthFromNow)
                    ->groupBy('giftId')
                    ->get();

//        if ($gift_logs) {
            foreach ($gift_logs as $gift_log) {
                $gift=Gift::find($gift_log?->giftId);
                if (!$gift) continue;
                $gift->use_count =$gift_log?->total ?? 0;
                $gift->save();
            }
//        }

    }
}
