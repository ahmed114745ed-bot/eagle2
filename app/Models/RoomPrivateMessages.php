<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomPrivateMessages extends Model
{
    use HasFactory;


    protected $table = 'room_private_messages';

    protected $fillable = [
        'id','from_user_id','to_user_id', 'message', 'price', 'created_at', 'updated_at'
    ];

    public function user()
    {
        return $this->hasOne(User::class);
    }
}
