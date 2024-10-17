<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomVisitor extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'room_id', 'created_at', 'updated_at'];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
