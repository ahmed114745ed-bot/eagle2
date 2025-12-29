<?php

namespace Modules\DynamicTheme\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Widget extends Model
{
    use HasFactory;

    protected $fillable = [
        'widget_type',
        'widget_key',
        'display_name',
        'description',
        'is_repeatable',
        'has_children',
        'min_app_version',
        'icon',
        'is_active',
        'parent_id',
        'has_pages'
    ];

    protected $casts = [
        'is_repeatable' => 'boolean',
        'has_children' => 'boolean',
        'is_active' => 'boolean',
        'has_pages' => 'boolean',

    ];

    /**
     * Get all themes for this widget
     */
    public function themes(): HasMany
    {
        return $this->hasMany(WidgetTheme::class);
    }

    /**
     * Get active themes for this widget
     */
    public function activeThemes(): HasMany
    {
        return $this->hasMany(WidgetTheme::class)->where('is_active', true);
    }

    /**
     * Get default theme for this widget
     */
    public function defaultTheme()
    {
        return $this->hasOne(WidgetTheme::class)->where('is_default', true);
    }

    /**
     * Get settings definitions for this widget
     */
    public function settingsDefinitions(): HasMany
    {
        return $this->hasMany(WidgetSettingsDefinition::class)->orderBy('order');
    }

    /**
     * Get primary settings definitions
     */
    public function primarySettings(): HasMany
    {
        return $this->hasMany(WidgetSettingsDefinition::class)
            ->where('setting_category', 'primary')
            ->orderBy('order');
    }

    /**
     * Get secondary settings definitions
     */
    public function secondarySettings(): HasMany
    {
        return $this->hasMany(WidgetSettingsDefinition::class)
            ->where('setting_category', 'secondary')
            ->orderBy('order');
    }

    /**
     * Get available actions for this widget
     */
    public function actions(): HasMany
    {
        return $this->hasMany(WidgetAction::class);
    }

    /**
     * Get screen widgets using this widget
     */
    public function screenWidgets(): HasMany
    {
        return $this->hasMany(ScreenWidget::class);
    }

    /**
     * Scope for active widgets
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by widget type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('widget_type', $type);
    }

    /**
     * Find widget by key
     */
    public static function findByKey(string $key): ?self
    {
        return static::where('widget_key', $key)->first();
    }

    public function parent()
    {
        return $this->belongsTo(Widget::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Widget::class, 'parent_id');
    }
}
