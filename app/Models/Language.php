<?php

namespace App\Models;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Language extends Model
{
    use HasFactory, TimestampsWithTimezone;

    protected $fillable = ['name', 'code', 'direction', 'is_enabled'];

    public static function boot()
    {
        parent::boot();

        self::saved(function () {
            Cache::put(
                'languages',
                self::where('is_enabled', true)
                    ->pluck('name', 'code')
                    ->toArray()
            );
        });

        self::deleted(function () {
            Cache::forget('languages');
        });
    }
}
