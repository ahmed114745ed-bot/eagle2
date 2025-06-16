<?php

namespace App\Traits;

use App\Helpers\Common;
use Illuminate\Support\Carbon;

trait TimestampsWithTimezone
{
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)
            ->setTimezone(request()->header('tz',  Common::timeZone()))
            ->format('Y-m-d H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)
            ->setTimezone(request()->header('tz',  Common::timeZone()))
            ->format('Y-m-d H:i:s');
    }
}
