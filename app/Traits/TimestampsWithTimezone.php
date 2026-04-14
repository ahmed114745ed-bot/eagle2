<?php

namespace App\Traits;

use Illuminate\Support\Carbon;

trait TimestampsWithTimezone
{
    /**
     * @param  mixed  $value
     * @return string
     */
    public function getCreatedAtAttribute($value)
    {
        $tz = $this->getTimezone();

        return Carbon::parse($value)->setTimezone($tz)->format('Y-m-d H:i:s');
    }

    /**
     * @param  mixed  $value
     * @return string
     */
    public function getUpdatedAtAttribute($value)
    {
        $tz = $this->getTimezone();

        return Carbon::parse($value)->setTimezone($tz)->format('Y-m-d H:i:s');
    }

    protected function getTimezone(): string
    {
        $tz = request()->header('tz', 'UTC');

        if (function_exists('isValidTimezone') && ! isValidTimezone($tz)) {
            return 'UTC';
        }

        if (! in_array($tz, timezone_identifiers_list())) {
            return 'UTC';
        }

        return $tz;
    }
}
