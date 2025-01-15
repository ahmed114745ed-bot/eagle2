<?php

namespace App\Models\File;

use Illuminate\Database\Eloquent\Model;
use App\Models\File\Attribute\FileAttribute;
use App\Models\File\Relationship\FileRelationship;
use Carbon\Carbon;

class File extends Model
{
    use FileRelationship,FileAttribute;

    protected $table;

    protected $fillable = [
        'message_id', 'user_id', 'name',
    ];

    protected $appends = [
        'file_details',
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
        $this->table = config('chat.table.files_table');
    }
}
