<?php

namespace Modules\DynamicTheme\Http\Controllers\Api\V1;

use Modules\DynamicTheme\Http\Controllers\Controller;
use Modules\DynamicTheme\Entities\ClientConfiguration;
use Modules\DynamicTheme\Entities\ConfigScreenOverride;
use Modules\DynamicTheme\Entities\ConfigWidgetOverride;
use Modules\DynamicTheme\Entities\ConfigAssetOverride;
use Modules\DynamicTheme\Entities\Screen;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Flutter-optimized Screen Controller
 * Returns data in columns/rows format for easy Flutter consumption
 */
class FlutterScreenController extends Controller
{
    /**
     * Get screen data in Flutter-friendly columns/rows format
     * 
     * @param string $screenKey
     * @param Request $request
     * @return JsonResponse
     */
    public function show(string $screenKey, Request $request): JsonResponse
    {
        $screen = Screen::where('screen_key', $screenKey)
            ->where('is_active', true)
            ->first();

        if (!$screen) {
            return response()->json([
                'success' => false,
                'message' => 'Screen not found',
            ], 404);
        }

        $clientId = $request->header('X-Client-ID', 'default');
        $requestedConfigId = $request->query('configuration_id');

        $activeConfig = $requestedConfigId 
            ? ClientConfiguration::find($requestedConfigId)
            : ClientConfiguration::getActiveForClient($clientId);

        if (!$activeConfig) {
            return $this->buildBaseScreenResponse($screen);
        }

        // Check screen visibility
        $screenOverride = ConfigScreenOverride::where('configuration_id', $activeConfig->id)
            ->where('screen_id', $screen->id)
            ->first();

        if ($screenOverride && !$screenOverride->is_visible) {
            return response()->json([
                'success' => false,
                'message' => 'Screen hidden in active configuration',
            ], 404);
        }

        // Get widget overrides with all relations
        $widgetOverrides = ConfigWidgetOverride::where('configuration_id', $activeConfig->id)
            ->where('screen_id', $screen->id)
            ->where('is_visible', true)
            ->orderBy('display_order')
            ->with([
                'screenWidget.widget',
                'selectedTheme.assets',
                'themeChildOverrides.themeChild',
                'themeChildOverrides.assetOverrides.asset',
            ])
            ->get();

        // Theme-level asset overrides
        $themeAssetOverrides = ConfigAssetOverride::where('configuration_id', $activeConfig->id)
            ->get()
            ->keyBy('theme_asset_id');

        return $this->buildColumnsRowsResponse($screen, $screenOverride, $widgetOverrides, $themeAssetOverrides);
    }

    /**
     * Build base screen response (no configuration)
     */
    private function buildBaseScreenResponse(Screen $screen): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'screen' => [
                    'screen_key' => $screen->screen_key,
                    'screen_name' => $screen->screen_name,
                    'background_color' => $screen->background_color,
                ],
                'widgets' => [
                    'columns' => $this->getWidgetColumns(),
                    'rows' => [],
                ],
                'children' => [
                    'columns' => $this->getChildColumns(),
                    'rows' => [],
                ],
                'assets' => [
                    'columns' => $this->getAssetColumns(),
                    'rows' => [],
                ],
            ],
            'meta' => [
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Build columns/rows response
     */
    private function buildColumnsRowsResponse(
        Screen $screen,
        ?ConfigScreenOverride $screenOverride,
        $widgetOverrides,
        $themeAssetOverrides
    ): JsonResponse {
        $widgetRows = [];
        $childRows = [];
        $assetRows = [];

        foreach ($widgetOverrides as $override) {
            $screenWidget = $override->screenWidget;
            $theme = $override->selectedTheme;

            // Widget row
            $widgetRows[] = [
                $screenWidget?->id,                                              // id
                $screenWidget?->widget?->widget_key,                             // widget_key
                $screenWidget?->widget?->widget_type,                            // widget_type
                $screenWidget?->widget?->display_name,                           // display_name
                $override->display_order ?? $screenWidget?->order ?? 0,          // display_order
                $override->is_visible,                                           // is_visible
                $override->selected_theme_id,                                    // theme_id
                $override->x ?? 0,                                               // x
                $override->y ?? 0,                                               // y
                $override->width ?? 350,                                         // width
                $override->height ?? 150,                                        // height
                $override->z_index ?? 0,                                         // z_index
                $override->opacity ?? 1,                                         // opacity
                $override->layout_mode ?? 'absolute',                            // layout_mode
                $override->layout_gap ?? 8,                                      // layout_gap
                $override->layout_padding ?? 8,                                  // layout_padding
                $override->child_width ?? 80,                                    // child_width
                $override->child_height ?? 100,                                  // child_height
                $override->infinite_scroll ?? false,                             // infinite_scroll
                $override->scroll_speed ?? 3,                                    // scroll_speed
                $override->background_color ?? 'transparent',                    // background_color
                $override->border_radius ?? 8,                                   // border_radius
                $theme?->theme_name,                                             // theme_name
                $theme?->theme_key,                                              // theme_key
            ];

            // Children rows
            foreach ($override->themeChildOverrides as $childOverride) {
                $isVisible = $childOverride->is_visible ?? 
                    ($childOverride->themeChild?->hide === null ? true : !$childOverride->themeChild->hide);
                
                if (!$isVisible) continue;

                $childRows[] = [
                    $childOverride->id,                                                    // id
                    $screenWidget?->id,                                                    // widget_id
                    $childOverride->theme_child_id,                                        // theme_child_id
                    $childOverride->themeChild?->child_key,                                // child_key
                    $childOverride->themeChild?->label,                                    // label
                    $isVisible,                                                            // is_visible
                    $childOverride->order ?? 0,                                            // order
                    $childOverride->action,                                                // action
                    $childOverride->position,                                              // position
                    $childOverride->width ?? $childOverride->themeChild?->width ?? 300,    // width
                    $childOverride->height ?? $childOverride->themeChild?->height ?? 200,  // height
                    $childOverride->x ?? $childOverride->themeChild?->x ?? 0,              // x
                    $childOverride->y ?? $childOverride->themeChild?->y ?? 0,              // y
                    $childOverride->rotation ?? 0,                                         // rotation
                    $childOverride->scale ?? 1,                                            // scale
                    $childOverride->opacity ?? 1,                                          // opacity
                    $childOverride->z_index ?? 0,                                          // z_index
                    $childOverride->background_color ?? 'transparent',                     // background_color
                    $childOverride->border_width ?? 0,                                     // border_width
                    $childOverride->border_style ?? 'solid',                               // border_style
                    $childOverride->border_color ?? 'transparent',                         // border_color
                    $childOverride->border_radius_tl ?? 0,                                 // border_radius_tl
                    $childOverride->border_radius_tr ?? 0,                                 // border_radius_tr
                    $childOverride->border_radius_bl ?? 0,                                 // border_radius_bl
                    $childOverride->border_radius_br ?? 0,                                 // border_radius_br
                ];

                // Asset rows for this child
                foreach ($childOverride->assetOverrides as $assetOverride) {
                    if ($assetOverride->is_visible === false) continue;

                    $globalOverride = $themeAssetOverrides->get($assetOverride->asset_id);
                    $resolvedFilePath = $globalOverride?->file_path 
                        ?? $assetOverride->file_path 
                        ?? $assetOverride->asset?->file_path;
                    
                    $resolvedFileUrl = $resolvedFilePath 
                        ? Storage::url($resolvedFilePath) 
                        : null;

                    $type = $assetOverride->type ?? $assetOverride->asset?->type;
                    $value = $type === 'file' 
                        ? $resolvedFilePath 
                        : ($globalOverride?->value_override ?? $assetOverride->text ?? $assetOverride->asset?->text);

                    $assetRows[] = [
                        $assetOverride->id,                                                      // id
                        $childOverride->id,                                                      // child_override_id
                        $assetOverride->asset_id,                                                // asset_id
                        $assetOverride->asset?->asset_key,                                       // asset_key
                        $assetOverride->asset?->asset_type,                                      // asset_type
                        $type,                                                                   // type (file/text)
                        $value,                                                                  // value
                        $resolvedFileUrl,                                                        // file_url
                        $assetOverride->asset?->default_url,                                     // default_url
                        $assetOverride->is_visible !== false,                                    // is_visible
                        $assetOverride->width ?? $assetOverride->asset?->width ?? 80,            // width
                        $assetOverride->height ?? $assetOverride->asset?->height ?? 80,          // height
                        $assetOverride->x ?? $assetOverride->asset?->x ?? 0,                     // x
                        $assetOverride->y ?? $assetOverride->asset?->y ?? 0,                     // y
                        $assetOverride->opacity ?? 1,                                            // opacity
                        $assetOverride->z_index ?? 0,                                            // z_index
                        $assetOverride->rotation ?? 0,                                           // rotation
                        $assetOverride->scale ?? 1,                                              // scale
                        $assetOverride->border_width ?? 0,                                       // border_width
                        $assetOverride->border_style ?? 'solid',                                 // border_style
                        $assetOverride->border_color ?? 'transparent',                           // border_color
                        $assetOverride->border_radius_tl ?? 0,                                   // border_radius_tl
                        $assetOverride->border_radius_tr ?? 0,                                   // border_radius_tr
                        $assetOverride->border_radius_bl ?? 0,                                   // border_radius_bl
                        $assetOverride->border_radius_br ?? 0,                                   // border_radius_br
                        // Text styling properties
                        $assetOverride->text_content ?? $assetOverride->text ?? $assetOverride->asset?->text, // text_content
                        $assetOverride->text_color ?? '#ffffff',                                 // text_color
                        $assetOverride->font_size ?? 14,                                         // font_size
                        $assetOverride->font_weight ?? 'normal',                                 // font_weight
                        $assetOverride->font_family ?? 'inherit',                                // font_family
                        $assetOverride->text_align ?? 'center',                                  // text_align
                        $assetOverride->line_height ?? 1.4,                                      // line_height
                        $assetOverride->letter_spacing ?? 0,                                     // letter_spacing
                        $assetOverride->text_shadow ?? 'none',                                   // text_shadow
                        $assetOverride->text_decoration ?? 'none',                               // text_decoration
                        $assetOverride->text_transform ?? 'none',                                // text_transform
                    ];
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'screen' => [
                    'screen_key' => $screen->screen_key,
                    'screen_name' => $screen->screen_name,
                    'background_color' => $screenOverride?->background_color ?? $screen->background_color,
                ],
                'widgets' => [
                    'columns' => $this->getWidgetColumns(),
                    'rows' => $widgetRows,
                ],
                'children' => [
                    'columns' => $this->getChildColumns(),
                    'rows' => $childRows,
                ],
                'assets' => [
                    'columns' => $this->getAssetColumns(),
                    'rows' => $assetRows,
                ],
            ],
            'meta' => [
                'widgets_count' => count($widgetRows),
                'children_count' => count($childRows),
                'assets_count' => count($assetRows),
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Widget columns definition
     */
    private function getWidgetColumns(): array
    {
        return [
            'id',
            'widget_key',
            'widget_type',
            'display_name',
            'display_order',
            'is_visible',
            'theme_id',
            'x',
            'y',
            'width',
            'height',
            'z_index',
            'opacity',
            'layout_mode',
            'layout_gap',
            'layout_padding',
            'child_width',
            'child_height',
            'infinite_scroll',
            'scroll_speed',
            'background_color',
            'border_radius',
            'theme_name',
            'theme_key',
        ];
    }

    /**
     * Child columns definition
     */
    private function getChildColumns(): array
    {
        return [
            'id',
            'widget_id',
            'theme_child_id',
            'child_key',
            'label',
            'is_visible',
            'order',
            'action',
            'position',
            'width',
            'height',
            'x',
            'y',
            'rotation',
            'scale',
            'opacity',
            'z_index',
            'background_color',
            'border_width',
            'border_style',
            'border_color',
            'border_radius_tl',
            'border_radius_tr',
            'border_radius_bl',
            'border_radius_br',
        ];
    }

    /**
     * Asset columns definition
     */
    private function getAssetColumns(): array
    {
        return [
            'id',
            'child_override_id',
            'asset_id',
            'asset_key',
            'asset_type',
            'type',
            'value',
            'file_url',
            'default_url',
            'is_visible',
            'width',
            'height',
            'x',
            'y',
            'opacity',
            'z_index',
            'rotation',
            'scale',
            'border_width',
            'border_style',
            'border_color',
            'border_radius_tl',
            'border_radius_tr',
            'border_radius_bl',
            'border_radius_br',
            // Text styling
            'text_content',
            'text_color',
            'font_size',
            'font_weight',
            'font_family',
            'text_align',
            'line_height',
            'letter_spacing',
            'text_shadow',
            'text_decoration',
            'text_transform',
        ];
    }
}
