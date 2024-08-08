<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomOwnerAchievement extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function roomTarget()
    {
        return $this->belongsToMany(RoomGiftTarget::class, 'target_id');
    }

}
