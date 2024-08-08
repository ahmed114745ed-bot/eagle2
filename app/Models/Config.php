<?php

namespace App\Models;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {

            $Keys = ['app_id', 'app_key', 'app_secret', 'app_cluster'];
            foreach ($Keys as $key) {
                if ($model->isDirty('value') && $model->name == $key) {
                    if ($model->name == $key) {
                        Cache::forget('pusher_config');
                        \Artisan::call('config:cache');
                        break;
                    }
                }
            }
        });
    }
}
