<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoinGameUserDailyAggregated extends Model
{
    use HasFactory;


    protected $table = 'coin_game_users_daily_aggregated';

    protected $fillable = [
        'user_id',
        'game_id',
        'date',
        'total_played',
        'total_loss',
        'total_win',
        'app_profit',
    ];


}
