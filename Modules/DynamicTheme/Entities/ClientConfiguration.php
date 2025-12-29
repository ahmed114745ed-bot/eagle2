<?php

namespace Modules\DynamicTheme\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * HIERARCHICAL CONFIGURATION STRUCTURE
     * ====================================
     * 1. SCREENS - Top level: which screens are visible and their display order
     * 2. WIDGETS - Widget level: widget visibility, theme selection, and display order
     * 3. THEMES - Theme settings for widgets (via widget overrides)
     * 4. CHILDREN - Theme children visibility and positioning
     * 5. ASSETS - Asset overrides (both direct assets and child assets)
     */

    /**
     * Get screen overrides for this configuration
     * Level 1: Manage which screens are shown and their display order
     */
    public function screenOverrides(): HasMany
    {
        return $this->hasMany(ConfigScreenOverride::class, 'configuration_id');
    }

    /**
     * Get widget overrides for this configuration
     * Level 2: Manage widget visibility, display order, and theme selection
     */
    public function widgetOverrides(): HasMany
    {
        return $this->hasMany(ConfigWidgetOverride::class, 'configuration_id');
    }

    /**
     * Get theme child overrides for this configuration
     * Level 3 & 4: Manage theme settings and child visibility/positioning
     */
    public function themeChildOverrides(): HasMany
    {
        return $this->hasMany(ConfigThemeChildOverride::class, 'configuration_id');
    }

    /**
     * Get asset overrides for this configuration
     * Level 5: Manage asset overrides (theme assets)
     */
    public function assetOverrides(): HasMany
    {
        return $this->hasMany(ConfigAssetOverride::class, 'configuration_id');
    }

    /**
     * Get child asset overrides for this configuration
     * Level 5: Manage child asset overrides
     */
    public function childAssetOverrides(): HasMany
    {
        return $this->hasMany(ConfigChildAssetOverride::class, 'configuration_id');
    }

    /**
     * Scope for a specific client
     */
    public function scopeForClient($query, string $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Scope for active configuration
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get active configuration for a client
     */
    public static function getActiveForClient(string $clientId): ?self
    {
        return static::forClient($clientId)->active()->first();
    }

    /**
     * Activate this configuration (deactivates others for same client)
     */
    public function activate(): bool
    {
        // Deactivate all other configurations for this client
        static::forClient($this->client_id)
            ->where('id', '!=', $this->id)
            ->update(['is_active' => false]);

        // Activate this one
        return $this->update(['is_active' => true]);
    }

    /**
     * Clone this configuration with a new name
     * Duplicates the entire hierarchy: Screens → Widgets → Themes → Children → Assets
     */
    public function duplicate(string $newName): self
    {
        $newConfig = $this->replicate();
        $newConfig->name = $newName;
        $newConfig->is_active = false;
        $newConfig->save();

        // 1. Clone screen overrides
        foreach ($this->screenOverrides as $override) {
            $newOverride = $override->replicate();
            $newOverride->configuration_id = $newConfig->id;
            $newOverride->save();
        }

        // 2. Clone widget overrides with their nested theme children
        foreach ($this->widgetOverrides as $widgetOverride) {
            $newWidgetOverride = $widgetOverride->replicate();
            $newWidgetOverride->configuration_id = $newConfig->id;
            $newWidgetOverride->save();

            // Clone theme children overrides for this widget
            foreach ($widgetOverride->themeChildOverrides as $childOverride) {
                $newChildOverride = $childOverride->replicate();
                $newChildOverride->configuration_id = $newConfig->id;
                $newChildOverride->config_widget_override_id = $newWidgetOverride->id;
                $newChildOverride->save();

                // 5. Clone child asset overrides
                foreach ($childOverride->assetOverrides as $childAsset) {
                    $newChildAsset = $childAsset->replicate();
                    $newChildAsset->configuration_id = $newConfig->id;
                    $newChildAsset->config_theme_child_override_id = $newChildOverride->id;

                    // Copy file if exists
                    if ($childAsset->file_path && \Storage::disk('public')->exists($childAsset->file_path)) {
                        $newPath = str_replace(
                            "config_{$this->id}",
                            "config_{$newConfig->id}",
                            $childAsset->file_path
                        );
                        \Storage::disk('public')->copy($childAsset->file_path, $newPath);
                        $newChildAsset->file_path = $newPath;
                    }

                    $newChildAsset->save();
                }
            }
        }

        // Clone theme asset overrides (copy files too)
        foreach ($this->assetOverrides as $override) {
            $newOverride = $override->replicate();
            $newOverride->configuration_id = $newConfig->id;

            // If there's a file, copy it
            if ($override->file_path && \Storage::disk('public')->exists($override->file_path)) {
                $newPath = str_replace(
                    "config_{$this->id}",
                    "config_{$newConfig->id}",
                    $override->file_path
                );
                \Storage::disk('public')->copy($override->file_path, $newPath);
                $newOverride->file_path = $newPath;
            }

            $newOverride->save();
        }

        return $newConfig->fresh()->load([
            'screenOverrides',
            'widgetOverrides.themeChildOverrides.assetOverrides',
            'assetOverrides',
        ]);
    }
}
