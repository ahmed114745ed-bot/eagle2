<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Banner extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeWhereIsNotSeen(Builder $query, $utcTimestamp): Builder
    {
        return $query->whereDate('publish_at', '>', Carbon::createFromTimestamp($utcTimestamp, 'utc'));
    }


}
