<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoinGameUserAggregated extends Model
{
    protected $table = 'coin_game_users_aggregated';
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function game()
    {
        return $this->belongsTo(AllGame::class, 'game_id');
    }
}
