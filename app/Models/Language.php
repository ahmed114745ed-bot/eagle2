<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Language extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'direction', 'is_enabled'];

    public static function boot()
    {
        parent::boot();
    
        static::saved(function () {
            Cache::put('languages', self::where('is_enabled', true)
                ->pluck('name', 'code')
                ->toArray()
            );
        });
    
        static::deleted(function () {
            Cache::forget('languages'); 
        });
    }
}


