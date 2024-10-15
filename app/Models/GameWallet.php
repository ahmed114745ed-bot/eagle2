<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;


class GameWallet extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function scopeFilterByMonth(Builder $query): Builder
    {
        return $query->whereMonth('created_at', now()->month);
    }
}
