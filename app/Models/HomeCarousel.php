<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class HomeCarousel extends Model
{
    protected $table = 'home_carousels';
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function room()
    {
        return $this->hasOne(Room::class, 'uid', 'owner_id');
    }




    protected static function boot()
    {
        parent::boot();


        static::creating(function ($model) {
           if($model->form != null)
           {
            $newDuration = Carbon::now();
            $duration = match ($model->form) {
                '1' => $model->input > 1 ? $newDuration->addHours($model->input): $newDuration->addMinute($model->input * 60),
                '2' => $newDuration->addDays($model->input),
                '3' => $newDuration->addMonths($model->input),
                default => null
            };
            $model->duration  = $duration->timestamp ;
        }
        });

        static::saving(function ($model) {
            $newDuration = Carbon::now();
            if($model->form != null)
            {
            if ($model->isDirty('input') || $model->isDirty('form')) {
                $duration = match ($model->form) {
                    '1' => $model->input > 1 ? $newDuration->addHours($model->input): $newDuration->addMinute($model->input * 60),
                    '2' => $newDuration->addDays($model->input),
                    '3' => $newDuration->addMonths($model->input),
                    default => null
                };
                $model->duration  = $duration->timestamp;
            }
        }
        });
    }
}
