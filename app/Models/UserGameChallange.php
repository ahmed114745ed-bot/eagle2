<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGameChallange extends Model
{
    use HasFactory;
    public function player_one()
    {
        return $this->belongsTo(User::class, 'player_one_id');
    }

    public function player_two()
    {
        return $this->belongsTo(User::class, 'player_two_id');
    }
}
