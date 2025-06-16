<?php

namespace App\Models;

use App\Traits\TimestampsWithTimezone;
use Artisan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Config extends Model
{
    use TimestampsWithTimezone;

    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();

        self::saving(function ($model) {
            if ($model->valueSelect) {
                unset($model->valueSelect);
            }
            if ($model->valueInteger) {
                unset($model->valueInteger);
            }
            $keys = ['pusher_app_id', 'pusher_app_key', 'pusher_app_secret', 'pusher_app_cluster'];
            if (in_array($model->name, $keys)) {
                Cache::forget('pusher_config');
                Artisan::call('config:cache');
            }
        });
    }
}
