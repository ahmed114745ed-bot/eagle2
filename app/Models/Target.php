<?php

namespace App\Models;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;

class Target extends Model
{
    use TimestampsWithTimezone;

    protected $fillable = [
        'id',
        'level',
        'diamonds',
        'minuts',
        'days',
        'hours',
        'usd',
        'agency_share',
        'moment',
        'reel',
        'gold',
        'coin',
        'img',
        'app_profit_percentage',
        'db_percentage',
        'under_edit',
        'edit_id'
    ];

    protected static function boot()
    {
        parent::boot();

        self::saving(function ($model) {

            if (isset($model->usd) && isset($model->agency_share) && isset($model->db_percentage)) {
                $model->app_profit_percentage = 100 - (float) $model->usd - (float) $model->agency_share - (float) $model->db_percentage;
            }
        });
    }

    public function edit()
    {
        return $this->hasOne(TargetEdit::class, 'id', 'edit_id');
    }
}
