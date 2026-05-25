<?php

namespace App\Observers;

use Modules\Vip\Entities\Vip;
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
        Cache::forget('vips_data');
        Cache::forget('levels_chunks');
        Cache::forget('room_levels_all');
        Cache::rememberForever('vips', fn () => Vip::all());
    }
}
