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
               // Cache key for the timezone setting
    $cacheKey = 'timezone';

    // Retrieve the timezone setting from cache, or fetch it from the database if not cached
    $timezone = \Cache::rememberForever($cacheKey, function () {
        $setting = \App\Models\Setting::where('key', 'timezone')->first();
        return $setting?->value ?? 'UTC';
    });

    // Get the timezone from the request header or use the cached setting
    $timeZone = request()->header('tz') ?? $timezone;

    // Parse the date and set the timezone
    return Carbon::parse($value)->setTimezone($timeZone)->format('Y-m-d H:i:s');
    }

    // Convert updated_at to the user's local time zone
    public function getUpdatedAtAttribute($value)
    {
               // Cache key for the timezone setting
    $cacheKey = 'timezone';

    // Retrieve the timezone setting from cache, or fetch it from the database if not cached
    $timezone = \Cache::rememberForever($cacheKey, function () {
        $setting = \App\Models\Setting::where('key', 'timezone')->first();
        return $setting?->value ?? 'UTC';
    });

    // Get the timezone from the request header or use the cached setting
    $timeZone = request()->header('tz') ?? $timezone;

    // Parse the date and set the timezone
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
