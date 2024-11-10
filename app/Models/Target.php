<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Target extends Model
{

    protected $fillable = [
        'id',
        'level',
        'diamonds',
        'minuts',
        'days',
        'hours',
        'usd',
        'agency_share',
        'moment',
        'reel',
        'gold',
        'coin',
        'img',
    ];
    //     public function setReelAttribute($values)
    // {
    //     $this->attributes['reel'] = implode(',', $values);
    // }
    // public function setMomentAttribute($values)
    // {
    //     $this->attributes['moment'] = implode(',', $values);
    // }
}
