<?php

namespace Modules\RoomBoom\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomBoomLevel extends Model
{
    protected $fillable = ['level', 'min_target', 'target'];

    public function roomBoomRewards(): HasMany
    {
        return $this->hasMany(RoomBoomReward::class);
    }
}
