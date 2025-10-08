<?php

namespace App\Models;

use Carbon\Carbon;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;
use Modules\Events\Entities\GeneralRole;
use Illuminate\Database\Eloquent\Casts\Attribute;

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
        'display_at' => 'array',

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
                // $model->duration = $duration->timestamp;
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
                    // $model->duration = $duration->timestamp;
                }
            }

            static::saving(function ($model) {
                if (is_array($model->display_at)) {
                    $model->display_at = json_encode($model->display_at);
                }
            });

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
                    // $model->duration = $duration->timestamp;
                }
            }
        });
    }


    public function displays()
    {
        return $this->hasMany(HomeCarouselDisplay::class, 'home_carousel_id');
    }

    public function getIsActiveAttribute()
    {
        return is_null($this->duration) || $this->duration > Carbon::now()->timestamp;
    }

    public function setDurationAttribute($value)
    {
        $this->attributes['duration'] = $value;
    }

    public function getDurationAttribute($value)
    {
        return $value;
    }

    public function displayDiscover(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->displays()->where('display_type', 'discover')->exists(),
            set: fn($value) => $this->syncDisplay('discover', $value)
        );
    }

    public function displayHomeTop(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->displays()->where('display_type', 'home_top')->exists(),
            set: fn($value) => $this->syncDisplay('home_top', $value)
        );
    }

    public function displayHomeMiddle(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->displays()->where('display_type', 'home_middle')->exists(),
            set: fn($value) => $this->syncDisplay('home_middle', $value)
        );
    }

    public function displayLive(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->displays()->where('display_type', 'live')->exists(),
            set: fn($value) => $this->syncDisplay('live', $value)
        );
    }

    public function displayCountry(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->displays()->where('display_type', 'country')->exists(),
            set: fn($value) => $this->syncDisplay('country', $value)
        );
    }




    protected function syncDisplay(string $type, $value)
    {
        if ($value) {
            $this->displays()->firstOrCreate(['display_type' => $type]);
        } else {
            $this->displays()->where('display_type', $type)->delete();
        }
    }





    protected function getDisplayAtAttribute($value)
    {
        $decoded = $this->displays?->pluck('display_type')->toArray();
        return is_array($decoded) ? $decoded : [];
    }



}
