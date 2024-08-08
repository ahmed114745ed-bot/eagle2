<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatSetting extends Model
{
    use HasFactory;
    protected $fillable= ['id','user_id','chat_with_friends','chat_with_followers','chat_with_all','chat_with_following'];
}
