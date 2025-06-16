<?php

namespace App\Models\Message;

use App\Models\Message\Relationship\MessageRelationship;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use MessageRelationship, TimestampsWithTimezone;

    protected $table;

    protected $fillable = [
        'user_id',
        'text',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('chat.table.messages_table');
    }
}
