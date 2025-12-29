<?php

namespace Modules\DynamicTheme\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Screen extends Model
{
    use HasFactory;

    protected $fillable = [
        'screen_key',
        'screen_name',
        'description',
        'display_order',
        'min_app_version',
        'layout',
        'is_active',
        'max_widgets',
    ];

    protected $casts = [
        'layout' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get all widgets for this screen
     */
    public function screenWidgets(): HasMany
    {
        return $this->hasMany(ScreenWidget::class)->orderBy('order');
    }

    /**
     * Alias for screenWidgets (for API compatibility)
     */
    public function widgets(): HasMany
    {
        return $this->screenWidgets();
    }

    /**
     * Get active widgets for this screen
     */
    public function activeWidgets(): HasMany
    {
        return $this->hasMany(ScreenWidget::class)
            ->where('is_active', true)
            ->orderBy('order');
    }

    /**
     * Get positioned (floating) widgets
     */
    public function positionedWidgets(): HasMany
    {
        return $this->hasMany(ScreenWidget::class)
            ->where('is_positioned', true)
            ->where('is_active', true)
            ->orderBy('order');
    }

    /**
     * Get non-positioned (regular) widgets
     */
    public function regularWidgets(): HasMany
    {
        return $this->hasMany(ScreenWidget::class)
            ->where('is_positioned', false)
            ->where('is_active', true)
            ->orderBy('order');
    }

    /**
     * Scope for active screens
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Find screen by key
     */
    public static function findByKey(string $key): ?self
    {
        return static::where('screen_key', $key)->first();
    }

      public function allowedWidgets()
    {
        return $this->belongsToMany(Widget::class, 'screen_allowed_widgets');
    }

    

    public function allowedWidgetsV2()
    {
        return $this->belongsToMany(
            Widget::class,
            'screen_allowed_widgets',     
            'screen_id',         
            'widget_id'          
        )->withTimestamps();
    }
}
