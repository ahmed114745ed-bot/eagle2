<?php

namespace Utd\Chat\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PinToTop extends Model
{
    use HasFactory;

    protected $fillable = ['chat_room_id', 'user_id'];
}
