<?php

namespace Modules\Badge\Entities;

use Illuminate\Database\Eloquent\Model;

class UserBadge extends Model
{
    protected $guarded = [];
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->where('expire', 0)->orWhere('expire', '>=', now()->timestamp);
        });
    }
}
