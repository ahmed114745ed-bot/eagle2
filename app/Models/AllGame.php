<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllGame extends Model
{
    use HasFactory;
    protected $guarded=['id'];

    public function coinGameUser()
    {
        return $this->hasMany(CoinGameUser::class,'game_id');
    }
}
