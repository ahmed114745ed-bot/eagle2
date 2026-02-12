<?php

namespace Utd\Room\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomCategory extends Model
{
    protected $table = 'room_categories';

    protected $guarded = [];

    public function typeRooms(): HasMany
    {
        return $this->hasMany(Room::class, 'room_type')
            ->select('id', 'numid', 'room_name', 'room_cover', 'room_intro');
    }

    public function classRooms(): HasMany
    {
        return $this->hasMany(Room::class, 'room_class')
            ->select('id', 'numid', 'room_name', 'room_cover', 'room_intro');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
}
