<?php

namespace Modules\Events\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

trait EventModel
{
    public function getStartDateAttribute($value)
    {
        $date = self::convertArabicNumbers($value);
        return Carbon::parse($date)->timezone(config('app.owner_timezone'))->copy()->toDateTimeString();
    }
    public function getEndDateAttribute($value)
    {
        return Carbon::parse($value)->timezone(config('app.owner_timezone'))->copy()->toDateTimeString();
    }

    public function getStartDateLocalAttribute()
    {
        return Carbon::parse($this->attributes['start_date'])->toDateString();
    }
    public function getEndDateLocalAttribute($value)
    {
        return Carbon::parse($this->attributes['end_date'])->toDateString();
    }


    public static function convertArabicNumbers($string)
    {
        $newNumbers = range(0, 9);
        $arabicNumbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        return str_replace($arabicNumbers, $newNumbers, $string);
    }

    public static function checkDateLanguage($date)
    {
        $arabicNumbersPattern = '/[٠-٩]/u'; // Arabic numerals
        $englishNumbersPattern = '/[0-9]/'; // English numerals

        if (preg_match($arabicNumbersPattern, $date)) {
            return 'arabic';
        }

        if (preg_match($englishNumbersPattern, $date)) {
            return 'english';
        }

        return 'unknown'; 
    }


    public function scopeCurrentEvent(Builder $query)
    {
        $timezone = config('app.owner_timezone') ?? '-03:00';
        $nowDate     = Carbon::now()->copy()->timezone($timezone)->toDateTimeString();
        return $query->whereRaw("start_date <= ?", [date($nowDate)]) // 27
            ->whereRaw("CONVERT_TZ(end_date, '+00:00', ?) >= ?", [$timezone, $nowDate]); // 27
    }


    public function scopePreviousEvent(Builder $query)
    {
        $timezone = config('app.owner_timezone') ?? '-03:00';
        $nowDate     = Carbon::now()->copy()->timezone($timezone)->toDateTimeString();

        return $query->whereRaw("start_date < ?", [date($nowDate)]) // 27
            ->whereRaw("CONVERT_TZ(end_date, '+00:00', ?) < ?", [$timezone, $nowDate]); // 27
    }


    public function scopeEndToday(Builder $query)
    {
        $timezone = config('app.owner_timezone');
        $nowDate     = Carbon::now()->copy()->toDateString();
        return $query->whereRaw("date(CONVERT_TZ(end_date, '+00:00', ?)) = ?", [$timezone, $nowDate]);
    }
}
