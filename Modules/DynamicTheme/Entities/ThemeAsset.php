<?php

namespace Modules\DynamicTheme\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThemeAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'theme_id',
        'child_id',
        'asset_key',
        'asset_label',
        'asset_type',
        'default_url',
        'file_path',
        'original_filename',
        'is_required',
        'order',
        'type',
        'text',
        'max_size'
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get the theme that owns this asset
     */
    public function theme(): BelongsTo
    {
        return $this->belongsTo(WidgetTheme::class, 'theme_id');
    }

    /**
     * Scope for required assets
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Scope by asset type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('asset_type', $type);
    }

    /**
     * Get asset types
     */
    public static function getAssetTypes(): array
    {
        return ['image', 'svga', 'vap', 'alpha'];
    }
}
