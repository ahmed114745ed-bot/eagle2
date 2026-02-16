<?php

namespace Utd\Chat\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_id2',
        'type',
        'user_1_deleted',
        'user_2_deleted',
    ];

    /**
     * Relations.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user2()
    {
        return $this->belongsTo(User::class, 'user_id2');
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class)->orderBy('id', 'desc');
    }

    /**
     * Scopes.
     */
    public function scopeBetweenUsers($query, $userId, $userId2)
    {
        return $query->where(function ($q) use ($userId, $userId2) {
            $q->where(function ($subQuery) use ($userId, $userId2) {
                $subQuery->where('user_id', $userId)
                    ->where('user_id2', $userId2);
            })->orWhere(function ($subQuery) use ($userId, $userId2) {
                $subQuery->where('user_id', $userId2)
                    ->where('user_id2', $userId);
            });
        });
    }
}
