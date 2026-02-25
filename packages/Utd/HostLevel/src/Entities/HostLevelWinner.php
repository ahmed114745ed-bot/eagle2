<?php

namespace Utd\HostLevel\Entities;

use App\Traits\HostLevelTrait;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HostLevelWinner extends Model
{
    use HasFactory, HostLevelTrait, TimestampsWithTimezone;

    protected $guarded = [];

    public function hostLevel()
    {
        return $this->belongsTo(HostLevel::class, 'host_level_id', 'id');
    }
}
