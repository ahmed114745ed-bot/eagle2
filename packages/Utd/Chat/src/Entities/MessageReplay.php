<?php

namespace Utd\Chat\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageReplay extends Model
{
    use HasFactory;

    protected $fillable = ['message_id', 'from_message_id'];

    public function from_message()
    {
        return $this->belongsTo(ChatMessage::class, 'from_message_id');
    }
}
