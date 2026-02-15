<?php

namespace Utd\Chat\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class React extends Model
{
    use HasFactory;

    protected $fillable = ['chat_room_id', 'chat_message_id', 'user_id', 'react'];

    public function scopeFindReact($query, $chatRoomId, $messageId, $userId)
    {
        return $query->where('chat_room_id', $chatRoomId)
            ->where('chat_message_id', $messageId)
            ->where('user_id', $userId);
    }
}
