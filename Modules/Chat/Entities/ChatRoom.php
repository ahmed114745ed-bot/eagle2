<?php

namespace Modules\Chat\Entities;

use App\Models\User;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatRoom extends Model
{
    use HasFactory, TimestampsWithTimezone;

    protected $guarded = [];

    public function messages()
    {
        return $this->hasMany(ChatMessage::class)  
                    ->orderBy('id', 'desc');
    }

    public function getLastMessageCreatedAtAttribute()
    {
        $lastMessage = $this->messages()->latest()->first();

        return $lastMessage ? $lastMessage->created_at : null;
    }

    public function unReadMessages()
    {
        return $this->hasMany(ChatMessage::class)->where('status', 'not Like', 'seen');
    }

    public function unreadMessagesFor($userId): HasMany
    {
        return $this->hasMany(ChatMessage::class)
            ->where('user_id', '<>', $userId)
            ->where('status', '<>', 'seen');
    }

    public function userOne()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function userTwo()
    {
        return $this->belongsTo(User::class, 'user_id2');
    }

    public function scopeBetweenUsers($query, $userId, $otherUserId)
    {
        return $query->where('user_id', $userId)->where('user_id2', $otherUserId)
            ->orWhere('user_id', $otherUserId)->where('user_id2', $userId);
    }
}
