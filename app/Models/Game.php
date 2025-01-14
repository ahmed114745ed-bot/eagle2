<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $guarded = ['id'];


    public function items()
    {
        return $this->hasMany(CoinGameUser::class, 'game_id', 'id');
    }
    
}
