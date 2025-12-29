<?php

namespace Modules\DynamicTheme\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibraryAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_key',
        'asset_label',
        'asset_type',
        'file_path',
        'default_url',
        'original_filename',
        'mime_type',
        'size',
        'uploaded_by',
    ];
}
