<?php

namespace App\Models\Conversation;

use App\Models\Conversation\Relationship\ConversationRelationship;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use ConversationRelationship,TimestampsWithTimezone;

    protected $table;

    protected $fillable = [
        'first_user_id',
        'second_user_id',
        'is_accepted',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('chat.table.conversations_table');
    }
}
