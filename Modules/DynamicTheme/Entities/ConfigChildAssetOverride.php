<?php

namespace Modules\DynamicTheme\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * ConfigChildAssetOverride
 * 
 * LEVEL 5: Child Asset Configuration
 * Manages asset overrides specifically for theme children (files and values)
 * 
 * These are asset overrides nested under theme children, connected via ConfigThemeChildOverride
 */
class ConfigChildAssetOverride extends Model
{
    use HasFactory;

    protected $table = 'config_child_asset_overrides';

    protected $fillable = [
        'configuration_id',
        'child_id',
        'asset_id',
        'type',
        'text',
        'file_path',
        'config_theme_child_override_id',
        'is_visible',
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
        'is_visible' => 'boolean',
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

    protected $appends = ['file_url'];

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
    public function child(): BelongsTo
    {
        return $this->belongsTo(ThemeChild::class, 'child_id');
    }

    /**
     * Get the asset
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(ThemeAsset::class, 'asset_id');
    }

    /**
     * Get the theme child override this asset belongs to
     */
    public function themeChildOverride(): BelongsTo
    {
        return $this->belongsTo(ConfigThemeChildOverride::class, 'config_theme_child_override_id');
    }

    /**
     * Get the file URL attribute
     */
    public function getFileUrlAttribute(): ?string
    {
        if ($this->file_path) {
            return \Illuminate\Support\Facades\Storage::url($this->file_path);
        }
        return null;
    }

    /**
     * Get the effective value (override or original)
     * Returns the override file URL, text override, or type
     */
    public function getEffectiveValue(): ?string
    {
        if ($this->file_path) {
            return $this->file_url;
        }
        if ($this->text) {
            return $this->text;
        }
        if ($this->type) {
            return $this->type;
        }
        return $this->asset?->file_url ?? $this->asset?->default_value;
    }

    /**
     * Delete the associated file when the override is deleted
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            if ($model->file_path && Storage::disk('public')->exists($model->file_path)) {
                Storage::disk('public')->delete($model->file_path);
            }
        });
    }
}

