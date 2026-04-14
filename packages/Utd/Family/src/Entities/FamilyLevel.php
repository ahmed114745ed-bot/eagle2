<?php

namespace Utd\Family\Entities;

use Cache;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TimestampsWithTimezone;

class FamilyLevel extends Model
{
    use TimestampsWithTimezone;

    protected $table = 'family_levels';

    protected $fillable = [
        'name',
        'img',
        'exp',
        'type',
        'members',
        'admins',
    ];

    protected static function booted(): void
    {
        // Clear cache when levels are created, updated, or deleted
        static::saved(fn () => Cache::forget('family_levels_all'));
        static::deleted(fn () => Cache::forget('family_levels_all'));
    }
}
