<?php

namespace Utd\Gifts\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GiftUpdateUsedCountWeakly extends Command
{
   
    protected $signature = 'update-gift-weakly:cron';

    protected $description = 'Reset and recalculate weekly gift usage counts';

    public function handle(): void
    {
        $giftModel = $this->resolveGiftModel();
        $giftLogModel = $this->resolveGiftLogModel();

        if (!$giftModel || !$giftLogModel) {
            $this->error('Could not resolve Gift or GiftLog model.');
            return;
        }

        $giftModel::query()->update(['use_count' => 0]);

        $oneMonthAgo = \Carbon\Carbon::now()->subMonth();
        $giftLogs = $giftLogModel::query()
            ->with('gift')
            ->select(['giftId', DB::raw('sum(giftNum) as total')])
            ->whereDate('created_at', '<=', date('Y-m-d'))
            ->whereDate('created_at', '>=', $oneMonthAgo)
            ->groupBy('giftId')
            ->get();

        foreach ($giftLogs as $log) {
            $gift = $giftModel::find($log->giftId);
            if (!$gift) {
                continue;
            }
            $gift->use_count = $log->total ?? 0;
            $gift->save();
        }

        $this->info('Weekly gift use_count updated successfully.');
    }

    private function resolveGiftModel(): ?string
    {
        foreach ([
            \Utd\Gifts\Entities\Gift::class,
            \App\Models\Gift::class,
        ] as $class) {
            if (class_exists($class)) {
                return $class;
            }
        }
        return null;
    }

    private function resolveGiftLogModel(): ?string
    {
        foreach ([
            \Utd\Gifts\Entities\GiftLog::class,
            \App\Models\GiftLog::class,
        ] as $class) {
            if (class_exists($class)) {
                return $class;
            }
        }
        return null;
    }
}
