<?php

namespace Modules\DynamicTheme\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WidgetTheme extends Model
{
    use HasFactory;

    protected $fillable = [
        'widget_id',
        'theme_key',
        'theme_name',
        'preview_image',
        'description',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the widget that owns this theme
     */
    public function widget(): BelongsTo
    {
        return $this->belongsTo(Widget::class);
    }

    /**
     * Get all assets for this theme
     */
    public function assets(): HasMany
    {
        return $this->hasMany(ThemeAsset::class, 'theme_id')->orderBy('order');
    }

    /**
     * Get required assets for this theme
     */
    public function requiredAssets(): HasMany
    {
        return $this->hasMany(ThemeAsset::class, 'theme_id')
            ->where('is_required', true)
            ->orderBy('order');
    }

    /**
     * Get screen widgets using this theme
     */
    public function screenWidgets(): HasMany
    {
        return $this->hasMany(ScreenWidget::class, 'theme_id');
    }

    /**
     * Scope for active themes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for default themes
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Find theme by key
     */
    public static function findByKey(string $key): ?self
    {
        return static::where('theme_key', $key)->first();
    }

    public function children()
    {
        return $this->hasMany(ThemeChild::class ,'theme_id')
            ->with('assets');
    }
}
