<?php

namespace Modules\DynamicTheme\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ConfiguredScreenResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $screen = $this->resource['screen'];
        $screenOverride = $this->resource['screen_override'] ?? null;
        $widgetOverrides = $this->resource['widget_overrides'] ?? collect();
        $themeAssetOverrides = $this->resource['theme_asset_overrides'] ?? collect();
        $includeHidden = (bool) ($this->resource['include_hidden'] ?? false);
        $activeConfigId = $this->resource['active_config_id'] ?? null;

        $widgets = $widgetOverrides
            ->map(function ($override) use ($themeAssetOverrides, $includeHidden, $activeConfigId) {
                $screenWidget = $override->screenWidget;
                $theme = $override->selectedTheme;

                $childOverrides = $override->themeChildOverrides
                    ->where('configuration_id', $activeConfigId);

                $children = $childOverrides
                    ->map(function ($childOverride) use ($themeAssetOverrides, $includeHidden) {
                        $overrideVisible = $childOverride->is_visible;
                        $defaultVisible = $childOverride->themeChild?->hide === null
                            ? true
                            : !$childOverride->themeChild->hide;

                        $isVisible = $overrideVisible !== null ? (bool) $overrideVisible : $defaultVisible;

                        if (!$includeHidden && !$isVisible) {
                            return null;
                        }

                        $childAssets = $childOverride->assetOverrides
                            ->filter(function ($assetOverride) use ($includeHidden) {
                                return $includeHidden || $assetOverride->is_visible !== false;
                            })
                            ->map(function ($assetOverride) use ($themeAssetOverrides) {
                                $globalOverride = $themeAssetOverrides->get($assetOverride->asset_id);

                                $resolvedFilePath = $globalOverride?->file_path
                                    ?? $assetOverride->file_path
                                    ?? $assetOverride->asset?->file_path;

                                $resolvedFileUrl = $resolvedFilePath
                                    ? Storage::url($resolvedFilePath)
                                    : ($assetOverride->asset?->file_path ? Storage::url($assetOverride->asset->file_path) : null);

                                return [
                                    'id' => $assetOverride->id,
                                    'asset_id' => $assetOverride->asset_id,
                                    'asset_key' => $assetOverride->asset?->asset_key,
                                    'asset_type' => $assetOverride->asset?->asset_type,
                                    'child_id' => $assetOverride->child_id,
                                    // 'type' => $assetOverride->type ?? $assetOverride->asset?->type,
                                    // 'text' => $globalOverride?->value_override ?? $assetOverride->text ?? $assetOverride->asset?->text,
                                    // 'file_path' => $resolvedFilePath,
                                        'type'  => $assetOverride->type ?? $assetOverride->asset?->type,
                                        'value' => (
                                            ($assetOverride->type ?? $assetOverride->asset?->type) === 'file'
                                                ? $resolvedFilePath
                                                : ($globalOverride?->value_override
                                                    ?? $assetOverride->text
                                                    ?? $assetOverride->asset?->text)
                                        ),
                                    'file_url' => $resolvedFileUrl,
                                    'default_url' => $assetOverride->asset?->default_url,
                                    'is_visible' => $assetOverride->is_visible !== false,
                                ];
                            })
                            ->values();

                        return [
                            'id' => $childOverride->id,
                            'theme_child_id' => $childOverride->theme_child_id,
                            'child_key' => $childOverride->themeChild?->child_key,
                            'label' => $childOverride->themeChild?->label,
                            'is_visible' => $isVisible,
                            'order' => $childOverride->order,
                            'action' => $childOverride->action,
                            'position' => $childOverride->position,
                            'assets' => $childAssets,
                        ];
                    })
                    ->filter()
                    ->sortBy('order')
                    ->values();

                $themeAssets = $theme?->assets?->map(function ($asset) use ($themeAssetOverrides) {
                    $override = $themeAssetOverrides->get($asset->id);
                    $resolvedPath = $override?->file_path ?? $asset->file_path;

                    return [
                        'id' => $asset->id,
                        'asset_key' => $asset->asset_key ?? $asset->asset_type,
                        'asset_label' => $asset->asset_label,
                        'asset_type' => $asset->asset_type,
                        'default_url' => $asset->default_url,
                        'file_path' => $resolvedPath,
                        'file_url' => $resolvedPath ? Storage::url($resolvedPath) : null,
                        'value_override' => $override?->value_override,
                        'original_filename' => $override?->original_filename ?? $asset->original_filename,
                    ];
                })->values();

                $settings = [
                    'theme_id' => $override->selected_theme_id ?? $screenWidget?->theme_id,
                    'primary_settings' => $override->primary_settings ?? $screenWidget?->primary_settings ?? [],
                    'secondary_settings' => $override->secondary_settings ?? $screenWidget?->secondary_settings ?? [],
                    'action' => $override->action ?? $screenWidget?->action ?? [],
                ];

                return [
                    'id' => $screenWidget?->id,
                    'widget_key' => $screenWidget?->widget?->widget_key,
                    'widget_type' => $screenWidget?->widget?->widget_type,
                    'display_name' => $screenWidget?->widget?->display_name,
                    'display_order' => $override->display_order ?? $screenWidget?->order ?? 0,
                    'is_visible' => $override->is_visible,
                    'selected_theme_id' => $override->selected_theme_id,
                    'selected_child_theme_id' => $override->selected_theme_id,
                    'settings' => $settings,
                    'theme' => $theme ? [
                        'id' => $theme->id,
                        'theme_name' => $theme->theme_name,
                        'theme_key' => $theme->theme_key,
                        'is_default' => $theme->is_default,
                        'children' => $children,
                        // 'assets' => $themeAssets,
                    ] : null,
                ];
            })
            ->filter(fn ($widget) => !is_null($widget['id']))
            ->values();

        return [
            'screen_key' => $screen->screen_key,
            'screen_name' => $screen->screen_name,
            'display_order' => $screenOverride->display_order ?? $screen->display_order ?? 0,
            'widgets' => $widgets,
        ];
    }
}
