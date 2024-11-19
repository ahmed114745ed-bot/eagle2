<?php

namespace Modules\Chat\Entities;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
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

    public function reacts()
    {
        return $this->hasMany(React::class);
    }

    public function albums()
    {
        return $this->hasMany(MessageAlbum::class);
    }

    public function scopeByUserInRoom($query, $chatRoomId, $userId){
        return $query->where('chat_room_id',$chatRoomId)->where('user_id',$userId);
    }



    public function scopeOwnedBy($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeInRoom($query, $roomId)
    {
        return $query->where('chat_room_id', $roomId);
    }

    public function canBeEdited()
    {
        $editDeadline = Carbon::parse($this->created_at)->addMinutes(15);
        return now()->lessThanOrEqualTo($editDeadline);
    }

    public function scopeEligibleForDeletion($query)
    {
        return $query->where('created_at', '>=', now()->subDay());
    }
}
