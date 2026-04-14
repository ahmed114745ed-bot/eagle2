<?php

namespace Utd\Events\Entities;

use App\Models\User;
use App\Support\PackageHelper;
use Utd\Gifts\Entities\Gift;
use App\Traits\CpWeeklyStar;
use App\Traits\EventModel;
use App\Traits\TimestampsWithTimezone;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Utd\CP\Entities\WeeklyCpGift;
use Utd\CP\Entities\WeeklyCpWinner;

class WeeklyStar extends Model
{
    use CpWeeklyStar, EventModel, HasFactory, SoftDeletes, TimestampsWithTimezone;

    protected $guarded = [];

    protected $appends = ['start_date_local', 'end_date_local'];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'editor_id');
    }

    public function gifts()
    {
        return PackageHelper::checkRelation($this, 'gift', 'belongsToMany')
            ?? $this->belongsToMany(Gift::class, 'weekly_star_gifts', 'weekly_star_id', 'gift_id');
    }

    public function rewards()
    {
        return $this->hasMany(Reward::class, 'weekly_star_id');
    }

    public function weeklyCpGifts()
    {
        return PackageHelper::checkRelation($this, 'cp', 'hasMany') ??
            $this->hasMany(WeeklyCpGift::class, 'weekly_cp_id');
    }

    public function WeeklyStarGifts()
    {
        return $this->hasMany(WeeklyStarGift::class, 'weekly_star_id');
    }

    public function WeeklyCpWinners()
    {
        return PackageHelper::checkRelation($this, 'cp', 'hasMany') ??
            $this->hasMany(WeeklyCpWinner::class, 'weekly_cp_id');
    }

    public function scopeWeeklyStar(Builder $query)
    {
        return $query->where('type', 'weekly_star');
    }

    public function scopePeriod(Builder $query)
    {
        return $query->where('type', 'event_period');
    }

    protected static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->start_date = self::convertArabicNumbers($model->attributes['start_date']);
            if ($model->type === 'event_period') {
                $model->end_date = self::convertArabicNumbers($model->attributes['end_date']);
            } else {
                $model->end_date = Carbon::createFromFormat('Y-m-d', $model->attributes['start_date'])->addWeek();
            }
            $model->admin_id = Auth::id();
        });

        self::saving(function ($model) {
            if ($model->isDirty('start_date')) {
                $model->start_date = self::convertArabicNumbers($model->attributes['start_date']);
                if ($model->type === 'event_period') {
                    $model->end_date = self::convertArabicNumbers($model->attributes['end_date']);
                } else {
                    $model->end_date = Carbon::createFromFormat('Y-m-d', $model->attributes['start_date'])->addWeek();
                }
                $model->editor_id = Auth::id();
            }
        });
    }

    protected static function convertArabicNumbers($string)
    {
        $newNumbers = range(0, 9);
        $arabicNumbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

        return str_replace($arabicNumbers, $newNumbers, $string);
    }
}
