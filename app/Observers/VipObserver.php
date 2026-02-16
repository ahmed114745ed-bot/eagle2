<?php

namespace App\Observers;

use Utd\Vip\Entities\Vip;
use Illuminate\Support\Facades\Cache;

class VipObserver
{
    public function saved(Vip $vip): void
    {
        $this->refreshVipCache();
    }

    public function deleted(Vip $vip): void
    {
        $this->refreshVipCache();
    }

    protected function refreshVipCache(): void
    {
        Cache::forget('vips');
        Cache::rememberForever('vips', fn () => Vip::all());
    }
}
