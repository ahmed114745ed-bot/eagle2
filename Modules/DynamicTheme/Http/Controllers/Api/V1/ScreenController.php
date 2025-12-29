<?php

namespace Modules\DynamicTheme\Http\Controllers\Api\V1;

use Modules\DynamicTheme\Http\Controllers\Controller;
use Modules\DynamicTheme\Http\Resources\ConfiguredScreenResource;
use Modules\DynamicTheme\Http\Resources\ScreenResource;
use Modules\DynamicTheme\Entities\ClientConfiguration;
use Modules\DynamicTheme\Entities\ConfigScreenOverride;
use Modules\DynamicTheme\Entities\ConfigWidgetOverride;
use Modules\DynamicTheme\Entities\ConfigAssetOverride;
use Modules\DynamicTheme\Entities\Screen;
use Modules\DynamicTheme\Services\ConfigurationHierarchyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScreenController extends Controller
{
    /**
     * Get all active screens
     */
    public function index(Request $request): JsonResponse
    {
        $clientId = $request->header('X-Client-ID', 'default');
        $requestedConfigId = $request->query('configuration_id');
        // include_hidden must be explicitly requested; configuration_id alone should not expose hidden children/widgets
        $includeHidden = $request->boolean('include_hidden', false);

        $activeConfig = null;
        if ($requestedConfigId) {
            $activeConfig = ClientConfiguration::find($requestedConfigId);
        }

        if (!$activeConfig) {
            $activeConfig = ClientConfiguration::getActiveForClient($clientId);
        }

        $includeHidden = $request->boolean('include_hidden', false);

        // If no active configuration, fall back to base screens
        if (!$activeConfig) {
            $screens = Screen::active()->get();
            return response()->json([
                'success' => true,
                'data' => $screens->map(fn($screen) => [
                    'screen_key' => $screen->screen_key,
                    'screen_name' => $screen->screen_name,
                    'min_app_version' => $screen->min_app_version,
                ]),
            ]);
        }

        $overrides = ConfigScreenOverride::where('configuration_id', $activeConfig->id)
            ->get()
            ->keyBy('screen_id');

        $screens = Screen::active()->get()
            ->filter(function ($screen) use ($overrides) {
                $ov = $overrides->get($screen->id);
                return $ov ? (bool)$ov->is_visible : true;
            })
            ->map(function ($screen) use ($overrides) {
                $ov = $overrides->get($screen->id);
                return [
                    'screen_key' => $screen->screen_key,
                    'screen_name' => $screen->screen_name,
                    'min_app_version' => $screen->min_app_version,
                    'display_order' => $ov->display_order ?? $screen->display_order ?? 0,
                ];
            })
            ->sortBy('display_order')
            ->values();

        return response()->json([
            'success' => true,
            'data' => $screens,
        ]);
    }

    /**
     * Get screen by key with all widgets
     */
    public function show(string $screenKey, Request $request)
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

        $activeConfig = null;
        if ($requestedConfigId) {
            $activeConfig = ClientConfiguration::find($requestedConfigId);
        }

        if (!$activeConfig) {
            $activeConfig = ClientConfiguration::getActiveForClient($clientId);
        }

        $includeHidden = $request->boolean('include_hidden', false);

        // No active configuration → fall back to base screen resource
        if (!$activeConfig) {
            return response()->json([
                'success' => true,
                'data' => new ScreenResource($screen),
                'meta' => [
                    'cache_ttl' => 300,
                    'version' => '1.0.0',
                    'generated_at' => now()->toIso8601String(),
                ],
            ]);
        }

        // Respect screen visibility in active configuration
        $screenOverride = ConfigScreenOverride::where('configuration_id', $activeConfig->id)
            ->where('screen_id', $screen->id)
            ->first();

        if ($screenOverride && !$screenOverride->is_visible) {
            return response()->json([
                'success' => false,
                'message' => 'Screen hidden in active configuration',
            ], 404);
        }

        // Widgets as per active configuration
        $widgetQuery = ConfigWidgetOverride::where('configuration_id', $activeConfig->id)
            ->where('screen_id', $screen->id)
            ->orderBy('display_order')
            ->with([
                'screenWidget.widget',
                'selectedTheme.assets',
                'themeChildOverrides.themeChild',
                'themeChildOverrides.assetOverrides.asset',
            ]);

        if (!$includeHidden) {
            $widgetQuery->where('is_visible', true);
        }

        $widgetOverrides = $widgetQuery->get();

        // Theme-level asset overrides for this configuration
        $themeAssetOverrides = ConfigAssetOverride::where('configuration_id', $activeConfig->id)
            ->get()
            ->keyBy('theme_asset_id');

        $resource = new ConfiguredScreenResource([
            'screen' => $screen,
            'screen_override' => $screenOverride,
            'widget_overrides' => $widgetOverrides,
            'theme_asset_overrides' => $themeAssetOverrides,
            'include_hidden' => $includeHidden,
            'active_config_id' => $activeConfig->id,
        ]);

        return $resource->additional([
            'success' => true,
            'meta' => [
                'cache_ttl' => 300,
                'version' => '1.0.0',
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }


     public function allowedWidgets(Screen $screen): JsonResponse
    {
        $widgets = $screen->allowedWidgetsV2()->with('themes')->get(); 
        return response()->json(['data' => $widgets]);
    }

    public function updateAllowedWidgets(Request $request, Screen $screen): JsonResponse
    {
        $validated = $request->validate([
            'widget_ids' => 'required|array',
            'widget_ids.*' => 'exists:widgets,id',
        ]);

        $screen->allowedWidgetsV2()->sync($validated['widget_ids']);

        return response()->json(['message' => 'Allowed widgets updated successfully']);
    }
}
