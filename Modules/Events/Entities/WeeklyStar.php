<?php

namespace Modules\Events\Entities;

use Carbon\Carbon;
use App\Models\Gift;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Modules\CP\Entities\WeeklyCpGift;
use Modules\Events\Traits\EventModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\CP\Entities\WeeklyCpWinner;

class WeeklyStar extends Model
{
    use HasFactory, SoftDeletes, EventModel;
    protected $guarded = ['id'];

    protected $appends = ['start_date_local', 'end_date_local'];

    public function getCreatedAtAttribute($value)
    {
        $timeZone = request()->header('tz') ?? 'UTC';
        //$timeZone = 'Asia/Dhaka'; // Get the user's time zone from the session
        return Carbon::parse($value)->setTimezone($timeZone)->format('Y-m-d H:i:s');
    }

    // Convert updated_at to the user's local time zone
    public function getUpdatedAtAttribute($value)
    {
        $timeZone = request()->header('tz') ?? 'UTC';
        //$timeZone = 'Asia/Dhaka'; // Get the user's time zone from the session
        return Carbon::parse($value)->setTimezone($timeZone)->format('Y-m-d H:i:s');
    }

    public function admin()
    {
        return $this->belongsTo(User::class,'admin_id');
    }

    public function editor()
    {
        return $this->belongsTo(User::class,'editor_id');
    }

    public function gifts()
    {
        return $this->belongsToMany(Gift::class,'weekly_star_gifts','weekly_star_id', 'gift_id');
    }

    public function rewards()
    {
        return $this->hasMany(Reward::class,'weekly_star_id');
    }

    public function weeklyCpGifts()
    {
        return $this->hasMany(WeeklyCpGift::class,'weekly_cp_id');
    }

    public function WeeklyStarGifts()
    {
        return $this->hasMany(WeeklyStarGift::class,'weekly_star_id');
    }
    public function WeeklyCpWinners()
    {
        return $this->hasMany(WeeklyCpWinner::class,'weekly_cp_id');
    }

    protected static function boot() {
        parent::boot();
        static::creating(function ($model) {
            $model->start_date = self::convertArabicNumbers($model->attributes['start_date']);
            if ($model->type == 'event_period'){
                $model->end_date =self::convertArabicNumbers($model->attributes['end_date']);
            }else{
                $model->end_date = Carbon::createFromFormat('Y-m-d', $model->attributes['start_date'])->addWeek();
            }
            $model->admin_id = Auth::id();
        });

        static::saving(function ($model) {
            if ($model->isDirty('start_date')) {
                $model->start_date = self::convertArabicNumbers($model->attributes['start_date']);
                if ($model->type == 'event_period'){
                    $model->end_date =self::convertArabicNumbers($model->attributes['end_date']);
                }else{
                    $model->end_date = Carbon::createFromFormat('Y-m-d', $model->attributes['start_date'])->addWeek();
                }
                $model->editor_id = Auth::id();
            }
        });
    }

//    public function getStartDateAttribute($value)
//    {
//        $date = self::convertArabicNumbers($value);
//        return Carbon::parse($date, '-03:00')->subDay()->startOfDay();
//
//    }
//    public function getEndDateAttribute($value)
//    {
//        return Carbon::parse($value)->subDay()->endOfDay();
//    }
//
//    public function getStartDateLocalAttribute()
//    {
//        return Carbon::parse($this->attributes['start_date'])->subDay()->toDateString();
//    }
//    public function getEndDateLocalAttribute($value)
//    {
//        return Carbon::parse($this->attributes['end_date'])->subDay()->toDateString();
//    }


    protected static function convertArabicNumbers($string) {
        $newNumbers = range(0, 9);
        $arabicNumbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        return str_replace($arabicNumbers, $newNumbers, $string);
    }


//    public function scopeCurrentEvent(Builder $query)
//    {
//        $timezone = '-03:00';
//        $nowDate     = Carbon::now()->copy()->timezone($timezone)->toDateTimeString();
//
//        return $query->whereRaw("date(CONVERT_TZ(start_date, '+00:00', ?)) <= ?", [$timezone, date($nowDate)] ) // 27
//                     ->whereRaw("CONVERT_TZ(end_date, '+00:00', ?) >= ?", [$timezone, $nowDate] ); // 27
//    }
//
//
//    public function scopePreviousEvent(Builder $query)
//    {
//        $timezone = '-03:00';
//        $nowDate     = Carbon::now()->copy()->timezone($timezone)->toDateTimeString();
//
//        return $query->whereRaw("date(CONVERT_TZ(start_date, '+00:00', ?)) < ?", [$timezone, date($nowDate)] ) // 27
//                     ->whereRaw("CONVERT_TZ(end_date, '+00:00', ?) < ?", [$timezone, $nowDate] ); // 27
//    }

    public function scopeWeeklyStar(Builder $query)
    {
        return $query->where('type', 'weekly_star');
    }

    public function scopePeriod(Builder $query)
    {
        return $query->where('type', 'event_period');
    }

    public function scopeWeeklyCP(Builder $query)
    {
        return $query->where('type', 'weekly_cp');
    }

}
