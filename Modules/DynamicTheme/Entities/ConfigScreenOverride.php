<?php

namespace Modules\DynamicTheme\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ConfigScreenOverride
 * 
 * LEVEL 1: Screen Configuration
 * Manages which screens are visible and their display order in the configuration
 * 
 * Hierarchy:
 * Configuration > Screen Override > Widget Overrides (nested)
 */
class ConfigScreenOverride extends Model
{
    use HasFactory;

    protected $fillable = [
        'configuration_id',
        'screen_id',
        'is_visible',
        'display_order',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'display_order' => 'integer',
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
     * Get all widget overrides for this screen (from the same configuration)
     * This allows querying widgets that belong to this screen within the configuration
     */
    public function configurationWidgetOverrides(): HasMany
    {
        return $this->hasMany(ConfigWidgetOverride::class, 'screen_id', 'screen_id')
            ->where('configuration_id', $this->configuration_id);
    }
}
