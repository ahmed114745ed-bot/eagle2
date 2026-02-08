<?php

namespace App\Traits;

/**
 * TimestampsWithTimezone Alias
 * 
 * توجه إلى Package Trait إذا كان موجوداً أو تستخدم النسخة المحلية
 */
if (trait_exists('\\Utd\\Gifts\\Traits\\TimestampsWithTimezone')) {
    // استخدم النسخة من Package
    class_alias('\\Utd\\Gifts\\Traits\\TimestampsWithTimezone', '\\App\\Traits\\TimestampsWithTimezone');
} else {
    // Fallback للنسخة المحلية
    use App\Helpers\Common;
    use Illuminate\Support\Carbon;

    trait TimestampsWithTimezone
    {
        public function getCreatedAtAttribute($value)
        {
            $tz = request()->header('tz', Common::timeZone());
            if (!isValidTimezone($tz)) {
                $tz = 'UTC';
            }

            return \Carbon\Carbon::parse($value)->setTimezone($tz)->format('Y-m-d H:i:s');
        }

        public function getUpdatedAtAttribute($value)
        {
            $tz = request()->header('tz', Common::timeZone());
            if (!in_array($tz, timezone_identifiers_list())) {
                $tz = 'UTC';
            }

            return Carbon::parse($value)->setTimezone($tz)->format('Y-m-d H:i:s');
        }

    }
}

