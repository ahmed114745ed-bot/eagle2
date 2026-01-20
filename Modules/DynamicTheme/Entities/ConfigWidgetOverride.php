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
        'auto_scroll',
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
        'auto_scroll' => 'boolean',
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
                'name' => $c->relationLoaded('themeChild') ? ($c->themeChild?->label ?? $c->themeChild?->child_key) : null,
                'child_key' => $c->relationLoaded('themeChild') ? $c->themeChild?->child_key : null,
                'is_visible' => $c->is_visible,
                'width' => $c->width,
                'height' => $c->height,
                'x' => $c->x,
                'y' => $c->y,
                'rotation' => $c->rotation,
                'scale' => $c->scale,
                'opacity' => $c->opacity,
                'z_index' => $c->z_index,
                'background_color' => $c->background_color,
                'border_width' => $c->border_width,
                'border_style' => $c->border_style,
                'border_color' => $c->border_color,
                'border_radius_tl' => $c->border_radius_tl,
                'border_radius_tr' => $c->border_radius_tr,
                'border_radius_bl' => $c->border_radius_bl,
                'border_radius_br' => $c->border_radius_br,
                'assets' => $c->relationLoaded('assetOverrides')
                    ? $c->assetOverrides->map(fn($a) => [
                        'id' => $a->asset_id,
                        'asset_key' => $a->asset?->asset_key ?? $a->asset?->name,
                        'name' => $a->asset?->name ?? $a->asset?->asset_key,
                        'file_url' => $a->file_url ?? $a->asset?->file_url,
                        'asset_type' => $a->asset?->asset_type ?? $a->asset?->type ?? 'image',
                        'text_content' => $a->text ?? $a->asset?->text,
                        'is_visible' => $a->is_visible,
                        'is_background' => $a->is_background ?? false,
                        'object_fit' => $a->object_fit ?? 'contain',
                        'width' => $a->width,
                        'height' => $a->height,
                        'x' => $a->x,
                        'y' => $a->y,
                        'opacity' => $a->opacity,
                        'z_index' => $a->z_index,
                        'rotation' => $a->rotation,
                        'scale' => $a->scale,
                        'border_width' => $a->border_width,
                        'border_style' => $a->border_style,
                        'border_color' => $a->border_color,
                        'border_radius_tl' => $a->border_radius_tl,
                        'border_radius_tr' => $a->border_radius_tr,
                        'border_radius_bl' => $a->border_radius_bl,
                        'border_radius_br' => $a->border_radius_br,
                    ])->values()->all()
                    : [],
            ])->values()->all()
            : [];

        return [
            'theme_id' => $this->selected_theme_id,
            'primary_settings' => $this->primary_settings ?? [],
            'secondary_settings' => $this->secondary_settings ?? [],
            'action' => $this->action ?? [],
            'children' => $children,
            // Visual Designer layout settings
            'x' => $this->x,
            'y' => $this->y,
            'width' => $this->width,
            'height' => $this->height,
            'z_index' => $this->z_index,
            'opacity' => $this->opacity,
            'layout_mode' => $this->layout_mode,
            'layout_gap' => $this->layout_gap,
            'layout_padding' => $this->layout_padding,
            'child_width' => $this->child_width,
            'child_height' => $this->child_height,
            'infinite_scroll' => $this->infinite_scroll,
            'auto_scroll' => $this->auto_scroll,
            'scroll_speed' => $this->scroll_speed,
            'background_color' => $this->background_color,
            'border_radius' => $this->border_radius,
        ];
    }
}
