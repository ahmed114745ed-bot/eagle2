<?php

namespace Utd\Room\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RoomSalary extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
