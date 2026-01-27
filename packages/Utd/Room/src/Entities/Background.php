<?php

namespace Utd\Room\Entities;

use Illuminate\Database\Eloquent\Model;

class Background extends Model
{
    protected $guarded = [];

    public function scopeEnabled($query)
    {
        return $query->where('enable', 1);
    }
}
