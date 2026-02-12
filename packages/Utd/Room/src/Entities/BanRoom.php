<?php

namespace Utd\Room\Entities;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BanRoom extends Model
{
    protected $table = 'bans_rooms';

    protected $guarded = [];

    public function room(): HasOne
    {
        return $this->hasOne(Room::class, 'id', 'room_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'staff_id');
    }
}
