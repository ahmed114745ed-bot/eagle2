<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KickRecord extends Model
{
    use HasFactory;
    protected $fillable=['id','kicked_user_id','user_id','room_id'];
}
