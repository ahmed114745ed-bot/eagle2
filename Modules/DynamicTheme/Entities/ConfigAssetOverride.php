<?php

namespace Modules\DynamicTheme\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * ConfigAssetOverride
 * 
 * LEVEL 5: Theme Asset Configuration
 * Manages asset overrides for widget themes (files and value overrides)
 * 
 * These are direct theme asset overrides, not child asset overrides
 */
class ConfigAssetOverride extends Model
{
    use HasFactory;

    protected $fillable = [
        'configuration_id',
        'theme_asset_id',
        'file_path',
        'original_filename',
        'value_override',
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
     * Get the original theme asset
     */
    public function themeAsset(): BelongsTo
    {
        return $this->belongsTo(ThemeAsset::class);
    }

    /**
     * Get the file URL attribute
     */
    public function getFileUrlAttribute(): ?string
    {
        if ($this->file_path) {
            return asset('storage/' . $this->file_path);
        }
        return null;
    }

    /**
     * Get the effective value (override or original)
     * Returns the override file URL, value override, or falls back to the original asset
        return $this->themeAsset?->file_url ?? $this->themeAsset?->default_value;
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
