<?php

namespace App\Models;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LuckyGift extends Model
{
    use HasFactory, TimestampsWithTimezone;

    protected $fillable = ['gift_id', 'win_probability'];

    protected $appends = ['min_percentag', 'mid_percentag', 'max_percentag'];

    public function getMinPercentagAttribute(): int
    {
        return (int) (@explode(',', $this->min_percentage)[0] ?? 0);
    }

    public function getMidPercentagAttribute(): int
    {
        return (int) (@explode(',', $this->min_percentage)[1] ?? 0);
    }

    public function getMaxPercentagAttribute(): int
    {
        return (int) (@explode(',', $this->min_percentage)[2] ?? 0);
    }

    public function setMinPercentagAttribute($value): void
    {
        $parts = explode(',', $this->min_percentage ?? '0,0,0');
        $parts[0] = $value;
        $this->min_percentage = implode(',', $parts);
    }

    public function setMidPercentagAttribute($value): void
    {
        $parts = explode(',', $this->min_percentage ?? '0,0,0');
        $parts[1] = $value;
        $this->min_percentage = implode(',', $parts);
    }

    public function setMaxPercentagAttribute($value): void
    {
        $parts = explode(',', $this->min_percentage ?? '0,0,0');
        $parts[2] = $value;
        $this->min_percentage = implode(',', $parts);
    }

    protected static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            if ($model->win_probability === null) {
                return false;
            }

            $model->min_percentage = $model->min_percentag.','.$model->mid_percentag.','.$model->max_percentag;

            unset($model->min_percentag);
            unset($model->mid_percentag);
            unset($model->max_percentag);
        });
        self::updating(function ($model) {
            if ($model->win_probability === null) {
                return false;
            }
            $model->min_percentage = $model->min_percentag.','.$model->mid_percentag.','.$model->max_percentag;

            unset($model->min_percentag);
            unset($model->mid_percentag);
            unset($model->max_percentag);
        });
    }
}
