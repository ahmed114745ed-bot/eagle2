<?php

namespace App\Models;

use App\Traits\TimestampsWithTimezone;
use Artisan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Config extends Model
{
    use TimestampsWithTimezone;

    protected $guarded = [];

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
                if (file_exists(base_path('bootstrap/cache/config.php'))) {
                    unlink(base_path('bootstrap/cache/config.php'));
                }
                Artisan::call('config:clear');
                Artisan::call('config:cache');
            }
        });
    }
}
