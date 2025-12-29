<?php
namespace Modules\DynamicTheme\Entities;

use Illuminate\Database\Eloquent\Model;

class ConfigThemeAssetOverride extends Model
{
    protected $fillable = [
        'configuration_id',
        'theme_asset_id',
        'override_url',
        'file_path',
        'original_filename'
    ];
}
