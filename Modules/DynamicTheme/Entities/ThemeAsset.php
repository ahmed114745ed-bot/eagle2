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
        'max_size',
        // Designer fields
        'width',
        'height',
        'x',
        'y',
        'rotation',
        'scale',
        'opacity',
        'z_index',
        'border_width',
        'border_style',
        'border_color',
        'border_radius_tl',
        'border_radius_tr',
        'border_radius_bl',
        'border_radius_br',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'order' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'x' => 'integer',
        'y' => 'integer',
        'rotation' => 'float',
        'scale' => 'float',
        'opacity' => 'float',
        'z_index' => 'integer',
        'border_width' => 'integer',
        'border_radius_tl' => 'integer',
        'border_radius_tr' => 'integer',
        'border_radius_bl' => 'integer',
        'border_radius_br' => 'integer',
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
