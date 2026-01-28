<?php

namespace App\Models;

use App\Support\PackageHelper;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;
use Utd\Room\Entities\Room;

class LiveTime extends Model
{
    use TimestampsWithTimezone;

    protected $table = 'live_times';

    protected $guarded = [];

    public function room()
    {
        return PackageHelper::checkRelation($this, 'room', 'belongsTo') ??
            $this->belongsTo(Room::class, 'uid', 'id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'uid', 'id');
    }
}
