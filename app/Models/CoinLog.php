<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoinLog extends Model
{
    protected $table = 'coin_logs';
    protected $guarded = ['id'];

    function user(){
        return $this->belongsTo(User::class , 'user_id')->with('profile');
    }
}
