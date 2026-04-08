<?php

namespace Utd\Gifts\Console;

use Illuminate\Console\Command;
use Utd\Gifts\Entities\Gift;

class GiftUpdateUsedCountMonthly extends Command
{
    protected $signature = 'update-gift-monthly:cron';

    protected $description = 'Disable least-used gifts at the end of each month';

    public function handle(): void
    {
        $gifts = Gift::orderBy('use_count', 'asc')
            ->where('enable', 1)
            ->take(5)
            ->get();

        foreach ($gifts as $gift) {
            $gift->enable = 2;
            $gift->save();
        }

        $this->info('Monthly gift usage processed successfully.');
    }
}
