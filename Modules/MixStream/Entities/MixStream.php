<?php

namespace Modules\MixStream\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MixStream extends Model
{
    protected $fillable = ['room_id'];

    public function rooms(): HasMany
    {
        return $this->hasMany(MixStreamRoom::class);
    }
}
