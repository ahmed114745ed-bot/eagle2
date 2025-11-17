<?php

namespace Modules\MixStream\Entities;

use App\Models\Room;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MixStreamRoom extends Model
{
    protected $fillable = ['mix_stream_id', 'room_id'];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
