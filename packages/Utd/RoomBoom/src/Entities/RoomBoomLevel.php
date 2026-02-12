<?php

namespace Utd\RoomBoom\Entities;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomBoomLevel extends Model
{
    use TimestampsWithTimezone;

    protected $fillable = ['level', 'min_target', 'target'];

    public function roomBoomRewards(): HasMany
    {
        return $this->hasMany(RoomBoomReward::class);
    }

    public function roomBooms(): HasMany
    {
        return $this->hasMany(RoomBoom::class);
    }
}
