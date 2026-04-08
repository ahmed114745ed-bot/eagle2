<?php

namespace Utd\Gifts\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Utd\Gifts\Entities\Gift;
use Utd\Gifts\Entities\GiftLog;

class GiftUpdateUsedCountWeakly extends Command
{
    protected $signature = 'update-gift-weakly:cron';

    protected $description = 'Reset and recalculate weekly gift usage counts';

    public function handle(): void
    {
        Gift::query()->update(['use_count' => 0]);

        $oneMonthAgo = Carbon::now()->subMonth();
        $giftLogs = GiftLog::query()
            ->with('gift')
            ->select(['giftId', DB::raw('sum(giftNum) as total')])
            ->whereDate('created_at', '<=', date('Y-m-d'))
            ->whereDate('created_at', '>=', $oneMonthAgo)
            ->groupBy('giftId')
            ->get();

        foreach ($giftLogs as $log) {
            $gift = Gift::find($log->giftId);
            if (! $gift) {
                continue;
            }
            $gift->use_count = $log->total ?? 0;
            $gift->save();
        }

        $this->info('Weekly gift use_count updated successfully.');
    }
}
