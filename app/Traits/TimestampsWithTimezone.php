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
        if ($formatted = $this->formatUsingPackageTrait(__FUNCTION__, $value)) {
            return $formatted;
        }

        return \Illuminate\Support\Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        if ($formatted = $this->formatUsingPackageTrait(__FUNCTION__, $value)) {
            return $formatted;
        }

        return \Illuminate\Support\Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    protected function formatUsingPackageTrait(string $method, $value)
    {
        if (! trait_exists(\Utd\Gifts\Traits\TimestampsWithTimezone::class)) {
            return null;
        }

        static $proxy = null;

        if ($proxy === null) {
            $proxy = new class {
                use \Utd\Gifts\Traits\TimestampsWithTimezone;

                public function format(string $method, $value)
                {
                    return $this->{$method}($value);
                }
            };
        }

        return $proxy->format($method, $value);
    }
}
