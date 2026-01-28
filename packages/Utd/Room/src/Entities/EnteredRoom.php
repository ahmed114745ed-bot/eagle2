<?php

namespace Utd\Room\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnteredRoom extends Model
{
    protected $table = 'entered_rooms';

    protected $guarded = [];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'rid', 'id');
    }
}
