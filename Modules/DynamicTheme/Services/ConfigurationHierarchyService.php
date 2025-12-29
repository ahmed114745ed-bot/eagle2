<?php

namespace Modules\DynamicTheme\Services;

use Modules\DynamicTheme\Entities\ClientConfiguration;
use Modules\DynamicTheme\Entities\ConfigAssetOverride;
use Modules\DynamicTheme\Entities\ConfigChildAssetOverride;
use Modules\DynamicTheme\Entities\ConfigScreenOverride;
use Modules\DynamicTheme\Entities\ConfigThemeChildOverride;
use Modules\DynamicTheme\Entities\ConfigWidgetOverride;
use Modules\DynamicTheme\Entities\Screen;
use Modules\DynamicTheme\Entities\ScreenWidget;
use Modules\DynamicTheme\Entities\ThemeAsset;
use Modules\DynamicTheme\Entities\ThemeChild;

/**
 * ConfigurationHierarchyService
 * 
 * Service for managing the hierarchical configuration structure
 * Provides easy access and manipulation of configuration overrides at all levels
 */
class ConfigurationHierarchyService
{
    /**
     * Get all configuration data with eager loading
     */
    public static function getFullConfiguration(int $configurationId): ?ClientConfiguration
    {
        return ClientConfiguration::with([
            'screenOverrides',
            'widgetOverrides.themeChildOverrides.assetOverrides',
            'assetOverrides',
        ])->find($configurationId);
    }

    /**
     * Get screen configuration for a specific screen
     */
    public static function getScreenOverride(int $configurationId, int $screenId): ?ConfigScreenOverride
    {
        return ConfigScreenOverride::where('configuration_id', $configurationId)
            ->where('screen_id', $screenId)
            ->with('configurationWidgetOverrides')
            ->first();
    }

    /**
     * Get all screens in configuration with their widgets
     */
    public static function getScreensWithWidgets(int $configurationId)
    {
        return ConfigScreenOverride::where('configuration_id', $configurationId)
            ->with('configurationWidgetOverrides.themeChildOverrides.assetOverrides')
            ->get();
    }

    /**
     * Get widget override
     */
    public static function getWidgetOverride(int $configurationId, int $screenWidgetId): ?ConfigWidgetOverride
    {
        return ConfigWidgetOverride::where('configuration_id', $configurationId)
            ->where('screen_widget_id', $screenWidgetId)
            ->with('themeChildOverrides.assetOverrides')
            ->first();
    }

    /**
     * Create or update screen override
     */
    public static function createOrUpdateScreenOverride(
        int $configurationId,
        int $screenId,
        array $data
    ): ConfigScreenOverride {
        return ConfigScreenOverride::updateOrCreate(
            [
                'configuration_id' => $configurationId,
                'screen_id' => $screenId,
            ],
            $data
        );
    }

    /**
     * Create or update widget override
     */
    public static function createOrUpdateWidgetOverride(
        int $configurationId,
        int $screenWidgetId,
        array $data
    ): ConfigWidgetOverride {
        return ConfigWidgetOverride::updateOrCreate(
            [
                'configuration_id' => $configurationId,
                'screen_widget_id' => $screenWidgetId,
            ],
            array_merge($data, ['configuration_id' => $configurationId])
        );
    }

    /**
     * Create or update theme child override
     */
    public static function createOrUpdateThemeChildOverride(
        int $configurationId,
        int $configWidgetOverrideId,
        int $themeChildId,
        array $data
    ): ConfigThemeChildOverride {
        return ConfigThemeChildOverride::updateOrCreate(
            [
                'configuration_id' => $configurationId,
                'config_widget_override_id' => $configWidgetOverrideId,
                'theme_child_id' => $themeChildId,
            ],
            $data
        );
    }

    /**
     * Create or update asset override
     */
    public static function createOrUpdateAssetOverride(
        int $configurationId,
        int $themeAssetId,
        array $data
    ): ConfigAssetOverride {
        return ConfigAssetOverride::updateOrCreate(
            [
                'configuration_id' => $configurationId,
                'theme_asset_id' => $themeAssetId,
            ],
            array_merge($data, ['configuration_id' => $configurationId])
        );
    }

    /**
     * Create or update child asset override
     */
    public static function createOrUpdateChildAssetOverride(
        int $configurationId,
        int $configThemeChildOverrideId,
        int $assetId,
        array $data
    ): ConfigChildAssetOverride {
        return ConfigChildAssetOverride::updateOrCreate(
            [
                'configuration_id' => $configurationId,
                'config_theme_child_override_id' => $configThemeChildOverrideId,
                'asset_id' => $assetId,
            ],
            array_merge($data, ['configuration_id' => $configurationId])
        );
    }

    /**
     * Get configuration statistics
     */
    public static function getConfigurationStats(int $configurationId): array
    {
        $config = ClientConfiguration::find($configurationId);

        if (!$config) {
            return [];
        }

        return [
            'total_screens' => $config->screenOverrides()->count(),
            'visible_screens' => $config->screenOverrides()->where('is_visible', true)->count(),
            'total_widgets' => $config->widgetOverrides()->count(),
            'visible_widgets' => $config->widgetOverrides()->where('is_visible', true)->count(),
            'total_children' => $config->themeChildOverrides()->count(),
            'visible_children' => $config->themeChildOverrides()->where('is_visible', true)->count(),
            'total_asset_overrides' => $config->assetOverrides()->count(),
            'total_child_asset_overrides' => $config->childAssetOverrides()->count(),
        ];
    }

    /**
     * Get all visible screens for a configuration
     */
    public static function getVisibleScreens(int $configurationId)
    {
        return ConfigScreenOverride::where('configuration_id', $configurationId)
            ->where('is_visible', true)
            ->orderBy('display_order')
            ->with([
                'screen',
                'configurationWidgetOverrides' => function ($query) {
                    $query->where('is_visible', true)
                        ->orderBy('display_order');
                }
            ])
            ->get();
    }

    /**
     * Get all visible widgets for a screen in a configuration
     */
    public static function getVisibleWidgetsForScreen(int $configurationId, int $screenId)
    {
        return ConfigWidgetOverride::where('configuration_id', $configurationId)
            ->where('screen_id', $screenId)
            ->where('is_visible', true)
            ->orderBy('display_order')
            ->with([
                'screenWidget',
                'selectedTheme',
                'themeChildOverrides' => function ($query) {
                    $query->where('is_visible', true)
                        ->orderBy('order');
                }
            ])
            ->get();
    }

    /**
     * Delete all overrides for a screen
     */
    public static function deleteScreenOverrides(int $configurationId, int $screenId): void
    {
        ConfigScreenOverride::where('configuration_id', $configurationId)
            ->where('screen_id', $screenId)
            ->delete();
    }

    /**
     * Delete all overrides for a widget
     */
    public static function deleteWidgetOverrides(int $configurationId, int $screenWidgetId): void
    {
        ConfigWidgetOverride::where('configuration_id', $configurationId)
            ->where('screen_widget_id', $screenWidgetId)
            ->delete();
    }

    /**
     * Delete all overrides for a theme child
     */
    public static function deleteThemeChildOverrides(int $configurationId, int $themeChildId): void
    {
        ConfigThemeChildOverride::where('configuration_id', $configurationId)
            ->where('theme_child_id', $themeChildId)
            ->delete();
    }
}
