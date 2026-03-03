<?php

namespace Utd\Gifts\Console\Commands;

use Illuminate\Console\Command;

class GiftUpdateUsedCountMonthly extends Command
{
   
    protected $signature = 'update-gift-monthly:cron';

    protected $description = 'Disable least-used gifts at the end of each month';

    public function handle(): void
    {
        $giftModel = $this->resolveGiftModel();

        if (!$giftModel) {
            $this->error('Could not resolve Gift model.');
            return;
        }

        $gifts = $giftModel::orderBy('use_count', 'asc')
            ->where('enable', 1)
            ->take(5)
            ->get();

        foreach ($gifts as $gift) {
            $gift->enable = 2;
            $gift->save();
        }

        $this->info('Monthly gift usage processed successfully.');
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
}
