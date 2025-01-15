<?php

namespace App\Models\Conversation;

use Illuminate\Database\Eloquent\Model;
use App\Models\Conversation\Relationship\ConversationRelationship;
use Carbon\Carbon;

class Conversation extends Model
{
    use ConversationRelationship;

    protected $table;

    protected $fillable = [
        'first_user_id', 'second_user_id', 'is_accepted',
    ];

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
    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('chat.table.conversations_table');
    }
}
