<?php

namespace Modules\DynamicTheme\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ConfigWidgetOverride
 * 
 * LEVEL 2: Widget Configuration
 * Manages widget visibility, display order, and theme selection within a screen
 * 
 * Hierarchy:
 * Configuration > Screen Override > Widget Override > Theme Child Overrides (nested)
 */
class ConfigWidgetOverride extends Model
{
    use HasFactory;

    protected $appends = ['settings'];

    protected $fillable = [
        'configuration_id',
        'screen_id',
        'screen_widget_id',
        'is_visible',
        'display_order',
        'selected_theme_id',
        'selected_child_theme_id',
        'widget_id',
        'primary_settings',
        'secondary_settings',
        'action',
        // Layout fields for Visual Designer
        'x',
        'y',
        'width',
        'height',
        'z_index',
        'opacity',
        'layout_mode',
        'layout_gap',
        'layout_padding',
        'child_width',
        'child_height',
        'infinite_scroll',
        'scroll_speed',
        'background_color',
        'border_radius',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'display_order' => 'integer',
        'primary_settings' => 'array',
        'secondary_settings' => 'array',
        'action' => 'array',
        // Layout field casts
        'x' => 'integer',
        'y' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'z_index' => 'integer',
        'opacity' => 'float',
        'layout_gap' => 'integer',
        'layout_padding' => 'integer',
        'child_width' => 'integer',
        'child_height' => 'integer',
        'infinite_scroll' => 'boolean',
        'scroll_speed' => 'integer',
        'border_radius' => 'integer',
    ];

    /**
     * Get the configuration
     */
    public function configuration(): BelongsTo
    {
        return $this->belongsTo(ClientConfiguration::class, 'configuration_id');
    }

    /**
     * Get the screen
     */
    public function screen(): BelongsTo
    {
        return $this->belongsTo(Screen::class);
    }

    /**
     * Get the screen widget
     */
    public function screenWidget(): BelongsTo
    {
        return $this->belongsTo(ScreenWidget::class);
    }

    /**
     * Get the selected theme
     */
    public function selectedTheme(): BelongsTo
    {
        return $this->belongsTo(WidgetTheme::class, 'selected_theme_id');
    }

    /**
     * Get widget
     */
    public function widget(): BelongsTo
    {
        return $this->belongsTo(Widget::class);
    }

    /**
     * Get theme child overrides for this widget
     * LEVEL 3 & 4: Manages theme children visibility and positioning
     */
    public function themeChildOverrides(): HasMany
    {
        return $this->hasMany(ConfigThemeChildOverride::class, 'config_widget_override_id');
    }

    /**
     * Combined settings payload for API responses
     */
    public function getSettingsAttribute(): array
    {
        $children = $this->relationLoaded('themeChildOverrides')
            ? $this->themeChildOverrides->map(fn($c) => [
                'theme_child_id' => $c->theme_child_id,
                'is_visible' => $c->is_visible,
            ])->values()->all()
            : [];

        return [
            'theme_id' => $this->selected_theme_id,
            'primary_settings' => $this->primary_settings ?? [],
            'secondary_settings' => $this->secondary_settings ?? [],
            'action' => $this->action ?? [],
            'children' => $children,
        ];
    }
}
