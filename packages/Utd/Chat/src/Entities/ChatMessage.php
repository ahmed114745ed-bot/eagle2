<?php

namespace Utd\Chat\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Utd\Chat\Traits\CreatedAtConvert;

class ChatMessage extends Model
{
    use CreatedAtConvert;
    use HasFactory;

    protected $fillable = [
        'chat_room_id',
        'user_id',
        'message',
        'room_owner_id',
        'room_id',
        'type',
        'file',
        'status',
        'user_1_deleted',
        'user_2_deleted',
        'duration',
    ];

    /**
     * Relations.
     */
    public function albums()
    {
        return $this->hasMany(MessageAlbum::class);
    }

    public function reacts()
    {
        return $this->hasMany(React::class);
    }

    public function chatRoom()
    {
        return $this->belongsTo(ChatRoom::class);
    }

    /**
     * Scopes.
     */
    public function scopeByUserInRoom($query, $chatRoomId, $userId)
    {
        return $query->where('chat_room_id', $chatRoomId)
            ->where('user_id', $userId);
    }

    public function scopeDistinctUserInRoom($query, $chatRoomId)
    {
        return $query->where('chat_room_id', $chatRoomId)
            ->distinct('user_id');
    }
}
