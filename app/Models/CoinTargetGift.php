<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoinTargetGift extends Model
{
    use HasFactory;
    protected $guarded = [];
    public $table = 'coins_target_gifts';


    public function vip()
    {
        return $this->belongsTo(OVip::class,'item_id');
    }
    public function ware()
    {
        return $this->belongsTo(Ware::class,'item_id');
    }
    protected static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            if ($model->coins) {
                unset($model->coins);
            }
            if ($model->achievement) {
                unset($model->achievement);
            }
        });

    }
}
