<?php

namespace App\Models;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllGame extends Model
{
    use HasFactory, TimestampsWithTimezone;

    protected $guarded = ['id'];

    public function coinGameUser()
    {
        return $this->hasMany(CoinGameUser::class, 'game_id');
    }

    public function getInRoomAttribute($value)
    {
        return $value === null ? 0 : $value;
    }
}
