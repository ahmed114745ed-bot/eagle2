<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LuckyGift extends Model
{
    use HasFactory;
    protected $fillable = ['gift_id', 'win_probability'];
    protected static function boot() {
        parent::boot();
        static::creating(function ($model) {
            if ($model->win_probability == null ) {
                return false;
            }

            $model->min_percentage =$model->min_percentag .','.$model->mid_percentag.','.$model->max_percentag;

            unset($model->min_percentag);
            unset($model->mid_percentag);
            unset($model->max_percentag);
        });
        static::updating(function ($model) {
            if ($model->win_probability == null ) {
                return false;
            }
            $model->min_percentage =$model->min_percentag .','.$model->mid_percentag.','.$model->max_percentag;

            unset($model->min_percentag);
            unset($model->mid_percentag);
            unset($model->max_percentag);
        });
    }
}
