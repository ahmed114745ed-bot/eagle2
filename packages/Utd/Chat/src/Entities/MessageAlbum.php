<?php

namespace Utd\Chat\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageAlbum extends Model
{
    use HasFactory;

    protected $fillable = ['chat_room_id', 'chat_message_id', 'user_id', 'file', 'type', 'frame'];

    public function message()
    {
        return $this->belongsTo(ChatMessage::class, 'chat_message_id');
    }
}
