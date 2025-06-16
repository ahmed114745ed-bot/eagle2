<?php

namespace App\Models;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vip extends Model
{
    use HasFactory, TimestampsWithTimezone;

    protected $fillable = [
        'type',
        'img',
        'exp',
        'level',
        'di',
        'co',
        'name_en',
        'name_ar',
    ];

    /*public function gifts()
    {
        return $this->hasMany(GiftRoomLevel::class,'level_id');
    }*/
}
