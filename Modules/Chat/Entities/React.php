<?php

namespace Modules\Chat\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class React extends Model
{
    use HasFactory;
    protected $guarded =['id'];

    public function room()
    {
        return $this->belongsTo(ChatRoom::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function message()
    {
        return $this->belongsTo(ChatMessage::class);
    }

    public function scopeFindReact($query, $chatRoomId, $messageId, $userId)
    {
        return $query->where('chat_room_id', $chatRoomId)
                     ->where('chat_message_id', $messageId)
                     ->where('user_id', $userId);
    }
}
