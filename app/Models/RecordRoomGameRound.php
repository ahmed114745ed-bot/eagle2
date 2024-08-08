<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecordRoomGameRound extends Model
{
    use HasFactory;
    protected $fillable=["id","record_room_game_id","round_number","player_win_id"];

}
