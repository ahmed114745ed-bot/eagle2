<?php

namespace Utd\Chat\Traits;

use App\Helpers\UserCommon;
use Carbon\Carbon;

trait CreatedAtConvert
{
    public function create_at($timeZone = null, $createdAt = null)
    {
        $createdAt = $createdAt ?? now();
        $createdAt = Carbon::parse($createdAt)->setTimezone($timeZone);

        if ($createdAt->isCurrentHour() || $createdAt->isCurrentDay()) {
            if (app()->getLocale() === 'ar') {
                return UserCommon::englishToArabicNumbers($createdAt);
            }

            return $createdAt->isoFormat('h:mm:ss A');
        }
        if ($createdAt->isYesterday()) {
            return __('messages.yesterday');
        }
        if ($createdAt->isCurrentWeek()) {
            $dayName = $createdAt->locale(app()->getLocale())->dayName; // ترجم اسم اليوم

            return $dayName;
        }
        if ($createdAt->isCurrentDay()) {
            $daysSinceCreation = $createdAt->diffInHours(Carbon::now());

            return __('messages.days_ago', ['days' => $daysSinceCreation]);
        }

        if (app()->getLocale() === 'ar') {
            return UserCommon::englishToArabicNumbersDate($createdAt);
        }

        return $createdAt->locale(app()->getLocale())->format('Y-m-d');

    }
}
