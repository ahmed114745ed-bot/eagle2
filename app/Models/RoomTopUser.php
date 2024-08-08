<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomTopUser extends Model
{
    use HasFactory;

    protected $table =  'room_top_users';

    protected $guarded = ['id'];

    public $timestamps = true;

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

}
