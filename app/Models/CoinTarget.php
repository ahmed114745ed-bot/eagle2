<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoinTarget extends Model
{
    use HasFactory;

    protected $guarded = [];
    public $table = 'coins_targets';


    public function gifts(){
        return $this->hasMany(CoinTargetGift::class, 'coin_target_id');
    }
}
