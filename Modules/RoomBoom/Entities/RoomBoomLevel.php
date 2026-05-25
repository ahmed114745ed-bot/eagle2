<?php

namespace Modules\RoomBoom\Entities;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomBoomLevel extends Model
{
    use TimestampsWithTimezone;

    protected $fillable = ['level', 'min_target', 'target','image_type'];

    protected static function booted(): void
    {
        static::saved(fn () => \Cache::forget('room_boo_levels'));
        static::saved(fn () => \Cache::forget('room_boom_levels'));
        static::saved(fn () => \Cache::forget('boom_levels:videos'));
        static::deleted(fn () => \Cache::forget('room_boo_levels'));
        static::deleted(fn () => \Cache::forget('room_boom_levels'));
        static::deleted(fn () => \Cache::forget('boom_levels:videos'));
    }

    public function roomBoomRewards(): HasMany
    {
        return $this->hasMany(RoomBoomReward::class);
    }

    public function roomBooms(): HasMany
    {
        return $this->hasMany(RoomBoom::class);
    }
}
