<?php

namespace App\Models;

use Carbon\Carbon;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;
use Modules\Events\Entities\GeneralRole;

class HomeCarousel extends Model
{
    use TimestampsWithTimezone;

    protected $table = 'home_carousels';

    protected $guarded = [];

    protected $casts = [
        'display_discover' => 'integer',
        'display_home_top' => 'integer',
        'display_home_middle' => 'integer',
        'display_live' => 'integer',
        'display_country' => 'integer',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function room()
    {
        return $this->hasOne(Room::class, 'uid', 'owner_id');
    }

    public function generalRole()
    {
        return $this->hasOne(GeneralRole::class, 'type', 'event_type');
    }

    public function countries()
    {
        return $this->belongsToMany(Country::class, 'banner_country');
    }

    public function countriesLite()
    {
        return $this->belongsToMany(Country::class, 'banner_country', 'home_carousel_id', 'country_id')
                    ->select(['countries.id', 'countries.name', 'countries.e_name', 'countries.flag'])
                    ->withPivot('home_carousel_id', 'country_id');
    }

    protected static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            if ($model->form !== null) {
                $newDuration = Carbon::now();
                $duration = match ($model->form) {
                    '1' => $model->input > 1 ? $newDuration->addHours($model->input) : $newDuration->addMinute($model->input * 60),
                    '2' => $newDuration->addDays($model->input),
                    '3' => $newDuration->addMonths($model->input),
                    default => null
                };
                $model->duration = $duration->timestamp;
            }
        });

        self::saving(function ($model) {
            $newDuration = Carbon::now();
            if ($model->form !== null) {
                if ($model->isDirty('input') || $model->isDirty('form')) {
                    $duration = match ($model->form) {
                        '1' => $model->input > 1 ? $newDuration->addHours($model->input) : $newDuration->addMinute($model->input * 60),
                        '2' => $newDuration->addDays($model->input),
                        '3' => $newDuration->addMonths($model->input),
                        default => null
                    };
                    $model->duration = $duration->timestamp;
                }
            }
        });

        self::updating(function ($model) {
            $newDuration = Carbon::now();
            if ($model->form !== null) {
                if ($model->isDirty('input') || $model->isDirty('form')) {
                    $duration = match ($model->form) {
                        '1' => $model->input > 1 ? $newDuration->addHours($model->input) : $newDuration->addMinute($model->input * 60),
                        '2' => $newDuration->addDays($model->input),
                        '3' => $newDuration->addMonths($model->input),
                        default => null
                    };
                    $model->duration = $duration->timestamp;
                }
            }
        });
    }



}
