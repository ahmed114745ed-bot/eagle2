<?php

namespace Modules\DynamicTheme\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ConfigThemeChildOverride
 * 
 * LEVEL 3 & 4: Theme Child Configuration
 * Manages theme children visibility, positioning, and ordering within a widget theme
 * 
 * Hierarchy:
 * Widget Override > Theme Child Override > Child Asset Overrides (nested)
 */
class ConfigThemeChildOverride extends Model
{
    use HasFactory;

    protected $fillable = [
        'configuration_id',
        'theme_child_id',
        'is_visible',
        'order',
        'action',
        'position',
        'config_widget_override_id',
        // Designer fields
        'width',
        'height',
        'x',
        'y',
        'rotation',
        'scale',
        'opacity',
        'z_index',
        'background_color',
        'border_width',
        'border_style',
        'border_color',
        'border_radius_tl',
        'border_radius_tr',
        'border_radius_bl',
        'border_radius_br',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
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
     * Get the configuration
     */
    public function configuration(): BelongsTo
    {
        return $this->belongsTo(ClientConfiguration::class, 'configuration_id');
    }

    /**
     * Get the theme child
     */
    public function themeChild(): BelongsTo
    {
        return $this->belongsTo(ThemeChild::class);
    }

    /**
     * Get the widget override this belongs to
     */
    public function widgetOverride(): BelongsTo
    {
        return $this->belongsTo(ConfigWidgetOverride::class, 'config_widget_override_id');
    }

    /**
     * Get asset overrides for this theme child
     * LEVEL 5: Manages asset overrides (files and values) for this child
     */
    public function assetOverrides(): HasMany
    {
        return $this->hasMany(ConfigChildAssetOverride::class, 'config_theme_child_override_id');
    }
}

