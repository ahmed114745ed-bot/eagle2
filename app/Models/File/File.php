<?php

namespace App\Models\File;

use App\Models\File\Attribute\FileAttribute;
use App\Models\File\Relationship\FileRelationship;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use FileAttribute, FileRelationship, TimestampsWithTimezone;

    protected $table;

    protected $fillable = [
        'message_id',
        'user_id',
        'name',
    ];

    protected $appends = [
        'file_details',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('chat.table.files_table');
    }
}
