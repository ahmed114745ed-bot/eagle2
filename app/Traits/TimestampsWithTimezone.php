<?php

namespace App\Traits;

/**
 * Alias for backward compatibility
 * @deprecated Use Utd\Gifts\Traits\TimestampsWithTimezone instead
 */
trait TimestampsWithTimezone
{
    /**
     * Use the package trait if available, otherwise define basic logic
     */
    public function getCreatedAtAttribute($value)
    {
        if (trait_exists(\Utd\Gifts\Traits\TimestampsWithTimezone::class)) {
            return \Utd\Gifts\Traits\TimestampsWithTimezone::getCreatedAtAttribute($value);
        }
        return \Illuminate\Support\Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        if (trait_exists(\Utd\Gifts\Traits\TimestampsWithTimezone::class)) {
            return \Utd\Gifts\Traits\TimestampsWithTimezone::getUpdatedAtAttribute($value);
        }
        return \Illuminate\Support\Carbon::parse($value)->format('Y-m-d H:i:s');
    }
}
