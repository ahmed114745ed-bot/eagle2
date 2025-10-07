<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class HomeCarouselDisplay extends Model
{
    use HasFactory;

    protected $table = 'home_carousel_displays';

    protected $fillable = [
        'home_carousel_id',
        'display_type',
        'created_at',
        'end_at',
        'duration',
        'duration_unit',
    ];

    protected $dates = [
        'created_at',
        'end_at',
    ];

    /**
     * العلاقة مع HomeCarousel
     */
    public function carousel()
    {
        return $this->belongsTo(HomeCarousel::class, 'home_carousel_id');
    }

  
    public function getRemainingTimeAttribute()
    {
        if ($this->end_at) {
            $now = Carbon::now();
            if ($now->gt($this->end_at)) {
                return 0; 
            }
            return $now->diffInSeconds($this->end_at);
        }
        return null; 
    }

    /**
     *
     * @param int $amount
     * @return void
     */
    public function addDuration(int $amount)
    {
        if (!$this->end_at) {
            $this->end_at = Carbon::now();
        }

        switch ($this->duration_unit) {
            case 'hours':
                $this->end_at->addHours($amount);
                break;
            case 'days':
                $this->end_at->addDays($amount);
                break;
            case 'months':
                $this->end_at->addMonths($amount);
                break;
        }

        $this->duration += $amount;
        $this->save();
    }


    protected static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            // dd($model);
        });

        self::saving(function ($model) {
            // dd($model);
        });

        self::updating(function ($model) {
        //   dd($model);
        });
    }
}
