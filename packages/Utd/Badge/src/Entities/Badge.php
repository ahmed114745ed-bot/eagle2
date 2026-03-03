<?php

namespace Utd\Badge\Entities;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    protected $guarded = [];

    public function images()
    {
        return $this->hasMany(BadgeImage::class);
    }
}
