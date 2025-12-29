<?php

namespace Modules\DynamicTheme\Http\Controllers;

use Modules\DynamicTheme\Http\Controllers\Controller;
use Modules\DynamicTheme\Entities\ClientConfiguration;
use Modules\DynamicTheme\Entities\ConfigAssetOverride;
use Modules\DynamicTheme\Entities\ConfigChildAssetOverride;
use Modules\DynamicTheme\Entities\ConfigScreenOverride;
use Modules\DynamicTheme\Entities\ConfigThemeChildOverride;
use Modules\DynamicTheme\Entities\ConfigWidgetOverride;
use Modules\DynamicTheme\Entities\ScreenWidget;
use Modules\DynamicTheme\Entities\ThemeAsset;
use Modules\DynamicTheme\Entities\ThemeChild;
use Modules\DynamicTheme\Services\ConfigurationHierarchyService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ConfigurationController extends Controller
{
    protected ConfigurationHierarchyService $configService;

    public function __construct(ConfigurationHierarchyService $configService)
    {
        $this->configService = $configService;
    }

    /**
     * Get all configurations for the current client
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $clientId = $request->user()->client_id ?? auth()->user()->client_id;
            
            $configurations = ClientConfiguration::where('client_id', $clientId)
                ->with(['screenOverrides', 'widgetOverrides'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($config) {
                    return [
                        'id' => $config->id,
                        'name' => $config->name,
                        'description' => $config->description,
                        'is_active' => $config->is_active,
                        'created_at' => $config->created_at,
                        'updated_at' => $config->updated_at,
                        'stats' => $this->getConfigurationStats($config->id),
                    ];
                });

            return response()->json([
                'status' => 'success',
                'configurations' => $configurations,
                'total' => count($configurations)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load configurations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single configuration with all details
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $configuration = ClientConfiguration::with([
                'screenOverrides.screen',
                'widgetOverrides.screenWidget.widget',
                'themeChildOverrides.themeChild',
                'assetOverrides.themeAsset',
                'childAssetOverrides.themeChild'
            ])->findOrFail($id);

            $fullConfig = $this->configService->getFullConfiguration($id);

            return response()->json([
                'status' => 'success',
                'configuration' => $configuration,
                'full_configuration' => $fullConfig,
                'stats' => $this->getConfigurationStats($id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Configuration not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Create new configuration
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'client_id' => 'required|integer'
            ]);

            $configuration = ClientConfiguration::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? '',
                'client_id' => $validated['client_id'],
                'is_active' => false
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Configuration created successfully',
                'configuration' => $configuration
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create configuration',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Update configuration
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $configuration = ClientConfiguration::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'sometimes|nullable|string'
            ]);

            $configuration->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Configuration updated successfully',
                'configuration' => $configuration
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update configuration',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Delete configuration
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $configuration = ClientConfiguration::findOrFail($id);
            
            // Delete all related data (cascade delete handled by model events)
            $configuration->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Configuration deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete configuration',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Clone configuration
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function clone(Request $request, int $id): JsonResponse
    {
        try {
            $sourceConfig = ClientConfiguration::findOrFail($id);
            
            $validated = $request->validate([
                'name' => 'required|string|max:255'
            ]);

            DB::beginTransaction();

            // Create new configuration
            $newConfig = ClientConfiguration::create([
                'name' => $validated['name'],
                'description' => $sourceConfig->description . ' (Clone)',
                'client_id' => $sourceConfig->client_id,
                'is_active' => false
            ]);

            // Clone all screen overrides
            foreach ($sourceConfig->screenOverrides as $screenOverride) {
                ConfigScreenOverride::create([
                    'configuration_id' => $newConfig->id,
                    'screen_id' => $screenOverride->screen_id,
                    'is_visible' => $screenOverride->is_visible,
                    'display_order' => $screenOverride->display_order
                ]);
            }

            // Clone all widget overrides with their children
            foreach ($sourceConfig->widgetOverrides as $widgetOverride) {
                $newWidgetOverride = ConfigWidgetOverride::create([
                    'configuration_id' => $newConfig->id,
                    'screen_widget_id' => $widgetOverride->screen_widget_id,
                    'theme_id' => $widgetOverride->theme_id,
                    'is_visible' => $widgetOverride->is_visible
                ]);

                // Clone theme child overrides
                foreach ($widgetOverride->themeChildOverrides as $childOverride) {
                    ConfigThemeChildOverride::create([
                        'config_widget_override_id' => $newWidgetOverride->id,
                        'theme_child_id' => $childOverride->theme_child_id,
                        'is_visible' => $childOverride->is_visible,
                        'position_x' => $childOverride->position_x,
                        'position_y' => $childOverride->position_y,
                        'display_order' => $childOverride->display_order
                    ]);
                }
            }

            // Clone asset overrides
            foreach ($sourceConfig->assetOverrides as $assetOverride) {
                ConfigAssetOverride::create([
                    'configuration_id' => $newConfig->id,
                    'theme_asset_id' => $assetOverride->theme_asset_id,
                    'value_type' => $assetOverride->value_type,
                    'value_text' => $assetOverride->value_text,
                    'value_file' => $assetOverride->value_file
                ]);
            }

            // Clone child asset overrides
            foreach ($sourceConfig->childAssetOverrides as $childAssetOverride) {
                ConfigChildAssetOverride::create([
                    'configuration_id' => $newConfig->id,
                    'theme_child_id' => $childAssetOverride->theme_child_id,
                    'theme_asset_id' => $childAssetOverride->theme_asset_id,
                    'value_type' => $childAssetOverride->value_type,
                    'value_text' => $childAssetOverride->value_text,
                    'value_file' => $childAssetOverride->value_file
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Configuration cloned successfully',
                'configuration' => $newConfig
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to clone configuration',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Activate configuration
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function activate(int $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Deactivate all other configurations for this client
            $configuration = ClientConfiguration::findOrFail($id);
            
            ClientConfiguration::where('client_id', $configuration->client_id)
                ->where('id', '!=', $id)
                ->update(['is_active' => false]);

            // Activate this configuration
            $configuration->update(['is_active' => true]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Configuration activated successfully',
                'configuration' => $configuration
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to activate configuration',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get configuration statistics
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function getStats(int $id): JsonResponse
    {
        try {
            $stats = $this->configService->getConfigurationStats($id);

            return response()->json([
                'status' => 'success',
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get configuration activity log
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function getActivity(int $id): JsonResponse
    {
        try {
            $configuration = ClientConfiguration::findOrFail($id);

            // Get activities from audit_logs table (if using Laravel Audit)
            // This is a simplified version - adjust based on your audit implementation
            $activities = DB::table('audit_logs')
                ->where('auditable_type', ClientConfiguration::class)
                ->where('auditable_id', $id)
                ->orderBy('created_at', 'desc')
                ->limit(100)
                ->get();

            return response()->json([
                'status' => 'success',
                'activities' => $activities
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get activity log',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Compare two configurations
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function compare(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'config1_id' => 'required|integer',
                'config2_id' => 'required|integer'
            ]);

            $config1 = ClientConfiguration::with([
                'screenOverrides',
                'widgetOverrides',
                'assetOverrides'
            ])->findOrFail($validated['config1_id']);

            $config2 = ClientConfiguration::with([
                'screenOverrides',
                'widgetOverrides',
                'assetOverrides'
            ])->findOrFail($validated['config2_id']);

            $comparison = [
                'config1' => [
                    'id' => $config1->id,
                    'name' => $config1->name,
                    'stats' => $this->getConfigurationStats($config1->id),
                    'screens_visible' => $config1->screenOverrides->where('is_visible', true)->count(),
                    'widgets_visible' => $config1->widgetOverrides->where('is_visible', true)->count(),
                ],
                'config2' => [
                    'id' => $config2->id,
                    'name' => $config2->name,
                    'stats' => $this->getConfigurationStats($config2->id),
                    'screens_visible' => $config2->screenOverrides->where('is_visible', true)->count(),
                    'widgets_visible' => $config2->widgetOverrides->where('is_visible', true)->count(),
                ],
                'differences' => $this->calculateDifferences($config1, $config2)
            ];

            return response()->json([
                'status' => 'success',
                'comparison' => $comparison
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to compare configurations',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    // HELPER METHODS

    /**
     * Get configuration statistics
     * 
     * @param int $configId
     * @return array
     */
    private function getConfigurationStats(int $configId): array
    {
        $screenOverrides = ConfigScreenOverride::where('configuration_id', $configId)->count();
        $widgetOverrides = ConfigWidgetOverride::where('configuration_id', $configId)->count();
        $themeChildOverrides = ConfigThemeChildOverride::whereHas('configWidgetOverride', function ($q) use ($configId) {
            $q->where('configuration_id', $configId);
        })->count();
        $assetOverrides = ConfigAssetOverride::where('configuration_id', $configId)->count();
        $childAssetOverrides = ConfigChildAssetOverride::where('configuration_id', $configId)->count();

        return [
            'total_screens' => $screenOverrides,
            'visible_screens' => ConfigScreenOverride::where('configuration_id', $configId)
                ->where('is_visible', true)->count(),
            'total_widgets' => $widgetOverrides,
            'visible_widgets' => ConfigWidgetOverride::where('configuration_id', $configId)
                ->where('is_visible', true)->count(),
            'total_children' => $themeChildOverrides,
            'visible_children' => ConfigThemeChildOverride::whereHas('configWidgetOverride', function ($q) use ($configId) {
                $q->where('configuration_id', $configId);
            })->where('is_visible', true)->count(),
            'total_asset_overrides' => $assetOverrides,
            'total_child_asset_overrides' => $childAssetOverrides,
            'visibility_percentage' => $this->calculateVisibilityPercentage($configId)
        ];
    }

    /**
     * Calculate visibility percentage
     * 
     * @param int $configId
     * @return float
     */
    private function calculateVisibilityPercentage(int $configId): float
    {
        $screenOverrides = ConfigScreenOverride::where('configuration_id', $configId)->get();
        if ($screenOverrides->isEmpty()) {
            return 0;
        }

        $visible = $screenOverrides->where('is_visible', true)->count();
        return round(($visible / $screenOverrides->count()) * 100, 2);
    }

    /**
     * Calculate differences between two configurations
     * 
     * @param ClientConfiguration $config1
     * @param ClientConfiguration $config2
     * @return array
     */
    private function calculateDifferences(ClientConfiguration $config1, ClientConfiguration $config2): array
    {
        // Screen differences
        $screens1 = $config1->screenOverrides->pluck('screen_id')->toArray();
        $screens2 = $config2->screenOverrides->pluck('screen_id')->toArray();
        
        // Widget differences
        $widgets1 = $config1->widgetOverrides->pluck('screen_widget_id')->toArray();
        $widgets2 = $config2->widgetOverrides->pluck('screen_widget_id')->toArray();

        return [
            'screens_only_in_config1' => array_diff($screens1, $screens2),
            'screens_only_in_config2' => array_diff($screens2, $screens1),
            'widgets_only_in_config1' => array_diff($widgets1, $widgets2),
            'widgets_only_in_config2' => array_diff($widgets2, $widgets1),
            'visibility_differences' => $this->getVisibilityDifferences($config1, $config2)
        ];
    }

    /**
     * Get visibility differences
     * 
     * @param ClientConfiguration $config1
     * @param ClientConfiguration $config2
     * @return array
     */
    private function getVisibilityDifferences(ClientConfiguration $config1, ClientConfiguration $config2): array
    {
        $differences = [];

        foreach ($config1->screenOverrides as $override1) {
            $override2 = $config2->screenOverrides->where('screen_id', $override1->screen_id)->first();
            
            if ($override2 && $override1->is_visible !== $override2->is_visible) {
                $differences[] = [
                    'type' => 'screen',
                    'id' => $override1->screen_id,
                    'config1_visible' => $override1->is_visible,
                    'config2_visible' => $override2->is_visible
                ];
            }
        }

        return $differences;
    }
}
