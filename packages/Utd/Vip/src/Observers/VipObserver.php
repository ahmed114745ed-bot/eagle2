<?php

namespace Utd\Vip\Observers;

use Illuminate\Support\Facades\Cache;
use Utd\Vip\Entities\Vip;

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
