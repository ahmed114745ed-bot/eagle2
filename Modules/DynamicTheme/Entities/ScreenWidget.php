<?php

namespace Modules\DynamicTheme\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScreenWidget extends Model
{
    use HasFactory;

    protected $fillable = [
        'screen_id',
        'widget_id',
        'theme_id',
        'order',
        'is_positioned',
        'position',
        'primary_settings',
        'secondary_settings',
        'action',
        'min_app_version',
        'is_active',
    ];

    protected $casts = [
        'order' => 'integer',
        'is_positioned' => 'boolean',
        'position' => 'array',
        'primary_settings' => 'array',
        'secondary_settings' => 'array',
        'action' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the screen that owns this widget
     */
    public function screen(): BelongsTo
    {
        return $this->belongsTo(Screen::class);
    }

    /**
     * Get the widget definition
     */
    public function widget(): BelongsTo
    {
        return $this->belongsTo(Widget::class);
    }

    /**
     * Get the theme for this widget
     */
    public function theme(): BelongsTo
    {
        return $this->belongsTo(WidgetTheme::class, 'theme_id');
    }

    /**
     * Get children for this widget (tabs, categories, etc.)
     */
    public function children(): HasMany
    {
        return $this->hasMany(ScreenWidgetChild::class)->orderBy('order');
    }

    /**
     * Get visible children
     */
    public function visibleChildren(): HasMany
    {
        return $this->hasMany(ScreenWidgetChild::class)
            ->where('is_visible', true)
            ->orderBy('order');
    }

    /**
     * Get special children (search, join_room buttons)
     */
    public function specialChildren(): HasMany
    {
        return $this->hasMany(ScreenWidgetChild::class)
            ->where('child_type', 'special')
            ->orderBy('order');
    }

    /**
     * Get regular children (tabs, categories)
     */
    public function regularChildren(): HasMany
    {
        return $this->hasMany(ScreenWidgetChild::class)
            ->where('child_type', '!=', 'special')
            ->where('is_visible', true)
            ->orderBy('order');
    }

    /**
     * Scope for active screen widgets
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for positioned (floating) widgets
     */
    public function scopePositioned($query)
    {
        return $query->where('is_positioned', true);
    }

    /**
     * Scope for regular (non-positioned) widgets
     */
    public function scopeRegular($query)
    {
        return $query->where('is_positioned', false);
    }

    /**
     * Get merged settings with defaults
     */
    public function getMergedPrimarySettings(): array
    {
        $defaults = [];

        if ($this->widget) {
            foreach ($this->widget->primarySettings as $setting) {
                $defaults[$setting->setting_key] = $setting->getCastedDefaultValue();
            }
        }

        return array_merge($defaults, $this->primary_settings ?? []);
    }

    /**
     * Get merged secondary settings with defaults
     */
    public function getMergedSecondarySettings(): array
    {
        $defaults = [];

        if ($this->widget) {
            foreach ($this->widget->secondarySettings as $setting) {
                $defaults[$setting->setting_key] = $setting->getCastedDefaultValue();
            }
        }

        return array_merge($defaults, $this->secondary_settings ?? []);
    }

    /**
     * Get assets from secondary settings
     */
    public function getAssetsAttribute(): array
    {
        $secondarySettings = $this->secondary_settings ?? [];
        return $secondarySettings['assets'] ?? [];
    }
}
