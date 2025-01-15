<?php

namespace Modules\Chat\Entities;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatRoom extends Model
{
    use HasFactory;
    protected $guarded =['id'];

    public function getCreatedAtAttribute($value)
    {
        $timeZone = request()->header('tz') ?? 'UTC';
        //$timeZone = 'Asia/Dhaka'; // Get the user's time zone from the session
        return Carbon::parse($value)->setTimezone($timeZone)->format('Y-m-d H:i:s');
    }

    // Convert updated_at to the user's local time zone
    public function getUpdatedAtAttribute($value)
    {
        $timeZone = request()->header('tz') ?? 'UTC';
        //$timeZone = 'Asia/Dhaka'; // Get the user's time zone from the session
        return Carbon::parse($value)->setTimezone($timeZone)->format('Y-m-d H:i:s');
    }
    public function messages()
    {
        return $this->hasMany(ChatMessage::class)->orderBy('id','desc');
    }
    public function getLastMessageCreatedAtAttribute()
    {
        $lastMessage = $this->messages()->latest()->first();
        return $lastMessage ? $lastMessage->created_at : null;
    }

    public function unReadMessages()
    {
        return $this->hasMany(ChatMessage::class)->where('status','not Like','seen');
    }

    public function userOne(){
        return $this->belongsTo(User::class,'user_id');
    }

    public function userTwo(){
        return $this->belongsTo(User::class,'user_id2');
    }

    public function scopeBetweenUsers($query, $userId, $otherUserId){

        return $query->where('user_id', $userId)->where('user_id2', $otherUserId)
        ->orWhere('user_id', $otherUserId)->where('user_id2', $userId);

    }
}
