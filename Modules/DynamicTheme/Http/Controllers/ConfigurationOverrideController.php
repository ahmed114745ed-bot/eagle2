<?php

namespace Modules\DynamicTheme\Http\Controllers;

use App\Helpers\Common;
use Modules\DynamicTheme\Entities\ClientConfiguration;
use Modules\DynamicTheme\Entities\ConfigScreenOverride;
use Modules\DynamicTheme\Entities\ConfigWidgetOverride;
use Modules\DynamicTheme\Entities\ConfigThemeChildOverride;
use Modules\DynamicTheme\Entities\ConfigAssetOverride;
use Modules\DynamicTheme\Entities\ConfigChildAssetOverride;
use Modules\DynamicTheme\Entities\Screen;
use Modules\DynamicTheme\Entities\ScreenWidget;
use Modules\DynamicTheme\Entities\ThemeAsset;
use Modules\DynamicTheme\Entities\ThemeChild;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ConfigurationOverrideController extends Controller
{
    // ======================== SCREEN OVERRIDES ========================

    /**
     * Get all screen overrides for a configuration
     * 
     * @param int $configId
     * @return JsonResponse
     */
    public function getScreenOverrides(int $configId): JsonResponse
    {
        try {
            $overrides = ConfigScreenOverride::where('configuration_id', $configId)
                ->with('screen')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $overrides
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get screen overrides',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save screen override
     * 
     * @param Request $request
     * @param int $configId
     * @return JsonResponse
     */
    public function saveScreenOverride(Request $request, int $configId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'screen_id' => 'required|integer|exists:screens,id',
                'is_visible' => 'nullable|boolean',
                'display_order' => 'nullable|integer|min:0',
                'background_color' => 'nullable|string|max:50',
            ]);

            // Prepare update data
            $updateData = [];
            if (isset($validated['is_visible'])) {
                $updateData['is_visible'] = $validated['is_visible'];
            }
            if (isset($validated['display_order'])) {
                $updateData['display_order'] = $validated['display_order'];
            }
            if (isset($validated['background_color'])) {
                $updateData['background_color'] = $validated['background_color'];
            }

            $override = ConfigScreenOverride::updateOrCreate(
                [
                    'configuration_id' => $configId,
                    'screen_id' => $validated['screen_id']
                ],
                array_merge([
                    'is_visible' => true,
                    'display_order' => 0
                ], $updateData)
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Screen override saved',
                'data' => $override
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save screen override',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Delete screen override
     * 
     * @param int $configId
     * @param int $screenId
     * @return JsonResponse
     */
    public function deleteScreenOverride(int $configId, int $screenId): JsonResponse
    {
        try {
            ConfigScreenOverride::where('configuration_id', $configId)
                ->where('screen_id', $screenId)
                ->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Screen override deleted'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete screen override',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    // ======================== WIDGET OVERRIDES ========================

    /**
     * Get all widget overrides for a configuration
     * 
     * @param int $configId
     * @return JsonResponse
     */
    public function getWidgetOverrides(int $configId): JsonResponse
    {
        try {
            $overrides = ConfigWidgetOverride::where('configuration_id', $configId)
                ->with(['screenWidget.widget', 'themeChildOverrides'])
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $overrides
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get widget overrides',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save widget override
     * 
     * @param Request $request
     * @param int $configId
     * @return JsonResponse
     */
    public function saveWidgetOverride(Request $request, int $configId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'screen_widget_id' => 'required|integer|exists:screen_widgets,id',
                'theme_id' => 'nullable|integer|exists:widget_themes,id',
                'is_visible' => 'required|boolean'
            ]);

            $override = ConfigWidgetOverride::updateOrCreate(
                [
                    'configuration_id' => $configId,
                    'screen_widget_id' => $validated['screen_widget_id']
                ],
                [
                    'theme_id' => $validated['theme_id'],
                    'is_visible' => $validated['is_visible']
                ]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Widget override saved',
                'data' => $override->load('screenWidget.widget', 'themeChildOverrides')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save widget override',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Delete widget override
     * 
     * @param int $configId
     * @param int $screenWidgetId
     * @return JsonResponse
     */
    public function deleteWidgetOverride(int $configId, int $screenWidgetId): JsonResponse
    {
        try {
            ConfigWidgetOverride::where('configuration_id', $configId)
                ->where('screen_widget_id', $screenWidgetId)
                ->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Widget override deleted'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete widget override',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    // ======================== THEME CHILD OVERRIDES ========================

    /**
     * Get all theme child overrides for a configuration
     * 
     * @param int $configId
     * @return JsonResponse
     */
    public function getThemeChildOverrides(int $configId): JsonResponse
    {
        try {
            $overrides = ConfigThemeChildOverride::whereHas('configWidgetOverride', function ($q) use ($configId) {
                $q->where('configuration_id', $configId);
            })
            ->with(['themeChild', 'configWidgetOverride'])
            ->get();

            return response()->json([
                'status' => 'success',
                'data' => $overrides
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get theme child overrides',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save theme child override
     * 
     * @param Request $request
     * @param int $configId
     * @return JsonResponse
     */
    public function saveThemeChildOverride(Request $request, int $configId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'config_widget_override_id' => 'required|integer|exists:config_widget_overrides,id',
                'theme_child_id' => 'required|integer|exists:theme_children,id',
                'is_visible' => 'required|boolean',
                'position_x' => 'nullable|numeric',
                'position_y' => 'nullable|numeric',
                'display_order' => 'nullable|integer|min:0'
            ]);

            // Verify the widget override belongs to this configuration
            $widgetOverride = ConfigWidgetOverride::findOrFail($validated['config_widget_override_id']);
            if ($widgetOverride->configuration_id !== $configId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Widget override does not belong to this configuration'
                ], 403);
            }

            $override = ConfigThemeChildOverride::updateOrCreate(
                [
                    'config_widget_override_id' => $validated['config_widget_override_id'],
                    'theme_child_id' => $validated['theme_child_id']
                ],
                [
                    'is_visible' => $validated['is_visible'],
                    'position_x' => $validated['position_x'] ?? 0,
                    'position_y' => $validated['position_y'] ?? 0,
                    'display_order' => $validated['display_order'] ?? 0
                ]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Theme child override saved',
                'data' => $override->load('themeChild')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save theme child override',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Delete theme child override
     * 
     * @param int $configId
     * @param int $childOverrideId
     * @return JsonResponse
     */
    public function deleteThemeChildOverride(int $configId, int $childOverrideId): JsonResponse
    {
        try {
            $childOverride = ConfigThemeChildOverride::findOrFail($childOverrideId);
            
            // Verify it belongs to this configuration
            if ($childOverride->configWidgetOverride->configuration_id !== $configId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Child override does not belong to this configuration'
                ], 403);
            }

            $childOverride->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Theme child override deleted'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete theme child override',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    // ======================== ASSET OVERRIDES ========================

    /**
     * Get all asset overrides for a configuration
     * 
     * @param int $configId
     * @return JsonResponse
     */
    public function getAssetOverrides(int $configId): JsonResponse
    {
        try {
            $overrides = ConfigAssetOverride::where('configuration_id', $configId)
                ->with('themeAsset')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $overrides
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get asset overrides',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save asset override
     * 
     * @param Request $request
     * @param int $configId
     * @return JsonResponse
     */
    public function saveAssetOverride(Request $request, int $configId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'theme_asset_id' => 'required|integer|exists:theme_assets,id',
                'value_type' => 'required|in:text,color,file',
                'value_text' => 'nullable|string',
                'value_file' => 'nullable|file|max:10240' // 10MB max
            ]);

            $asset = ConfigAssetOverride::where('configuration_id', $configId)
                ->where('theme_asset_id', $validated['theme_asset_id'])
                ->first();

            $disk = config('filesystems.default');

            if (!$asset) {
                $asset = new ConfigAssetOverride([
                    'configuration_id' => $configId,
                    'theme_asset_id' => $validated['theme_asset_id']
                ]);
            }

            $asset->value_type = $validated['value_type'];

            // Handle file upload
            if ($request->hasFile('value_file')) {
                // Delete old file if exists
                if ($asset->value_file && Storage::disk($disk)->exists($asset->value_file)) {
                    Storage::disk($disk)->delete($asset->value_file);
                }

                $asset->value_file = Common::upload("configs/{$configId}/assets", $request->file('value_file'), $disk);
            }

            // Set text value
            if ($validated['value_type'] === 'text' || $validated['value_type'] === 'color') {
                $asset->value_text = $validated['value_text'] ?? '';
            }

            $asset->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Asset override saved',
                'data' => $asset
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save asset override',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Delete asset override
     * 
     * @param int $configId
     * @param int $themeAssetId
     * @return JsonResponse
     */
    public function deleteAssetOverride(int $configId, int $themeAssetId): JsonResponse
    {
        try {
            $override = ConfigAssetOverride::where('configuration_id', $configId)
                ->where('theme_asset_id', $themeAssetId)
                ->first();

            if ($override) {
                // Delete file if exists
                $disk = config('filesystems.default');

                if ($override->value_file && Storage::disk($disk)->exists($override->value_file)) {
                    Storage::disk($disk)->delete($override->value_file);
                }

                $override->delete();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Asset override deleted'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete asset override',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    // ======================== CHILD ASSET OVERRIDES ========================

    /**
     * Get all child asset overrides for a configuration
     * 
     * @param int $configId
     * @return JsonResponse
     */
    public function getChildAssetOverrides(int $configId): JsonResponse
    {
        try {
            $overrides = ConfigChildAssetOverride::where('configuration_id', $configId)
                ->with(['themeChild', 'themeAsset'])
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $overrides
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get child asset overrides',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save child asset override
     * 
     * @param Request $request
     * @param int $configId
     * @return JsonResponse
     */
    public function saveChildAssetOverride(Request $request, int $configId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'theme_child_id' => 'required|integer|exists:theme_children,id',
                'theme_asset_id' => 'required|integer|exists:theme_assets,id',
                'value_type' => 'required|in:text,color,file',
                'value_text' => 'nullable|string',
                'value_file' => 'nullable|file|max:10240'
            ]);

            $asset = ConfigChildAssetOverride::where('configuration_id', $configId)
                ->where('theme_child_id', $validated['theme_child_id'])
                ->where('theme_asset_id', $validated['theme_asset_id'])
                ->first();

            $disk = config('filesystems.default');

            if (!$asset) {
                $asset = new ConfigChildAssetOverride([
                    'configuration_id' => $configId,
                    'theme_child_id' => $validated['theme_child_id'],
                    'theme_asset_id' => $validated['theme_asset_id']
                ]);
            }

            $asset->value_type = $validated['value_type'];

            // Handle file upload
            if ($request->hasFile('value_file')) {
                if ($asset->value_file && Storage::disk($disk)->exists($asset->value_file)) {
                    Storage::disk($disk)->delete($asset->value_file);
                }

                $asset->value_file = Common::upload("configs/{$configId}/child-assets", $request->file('value_file'), $disk);
            }

            // Set text value
            if ($validated['value_type'] === 'text' || $validated['value_type'] === 'color') {
                $asset->value_text = $validated['value_text'] ?? '';
            }

            $asset->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Child asset override saved',
                'data' => $asset
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save child asset override',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Delete child asset override
     * 
     * @param int $configId
     * @param int $childAssetOverrideId
     * @return JsonResponse
     */
    public function deleteChildAssetOverride(int $configId, int $childAssetOverrideId): JsonResponse
    {
        try {
            $override = ConfigChildAssetOverride::where('configuration_id', $configId)
                ->find($childAssetOverrideId);

            if ($override) {
                $disk = config('filesystems.default');

                if ($override->value_file && Storage::disk($disk)->exists($override->value_file)) {
                    Storage::disk($disk)->delete($override->value_file);
                }

                $override->delete();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Child asset override deleted'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete child asset override',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    // ======================== BULK OPERATIONS ========================

    /**
     * Bulk update visibility for screens/widgets
     * 
     * @param Request $request
     * @param int $configId
     * @return JsonResponse
     */
    public function bulkUpdateVisibility(Request $request, int $configId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'type' => 'required|in:screens,widgets,children',
                'ids' => 'required|array',
                'ids.*' => 'integer',
                'is_visible' => 'required|boolean'
            ]);

            DB::beginTransaction();

            $count = 0;

            if ($validated['type'] === 'screens') {
                $count = ConfigScreenOverride::where('configuration_id', $configId)
                    ->whereIn('screen_id', $validated['ids'])
                    ->update(['is_visible' => $validated['is_visible']]);
            } elseif ($validated['type'] === 'widgets') {
                $count = ConfigWidgetOverride::where('configuration_id', $configId)
                    ->whereIn('screen_widget_id', $validated['ids'])
                    ->update(['is_visible' => $validated['is_visible']]);
            } elseif ($validated['type'] === 'children') {
                $count = ConfigThemeChildOverride::whereIn('id', $validated['ids'])
                    ->whereHas('configWidgetOverride', function ($q) use ($configId) {
                        $q->where('configuration_id', $configId);
                    })
                    ->update(['is_visible' => $validated['is_visible']]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Updated {$count} items",
                'updated_count' => $count
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to bulk update visibility',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Bulk reorder items
     * 
     * @param Request $request
     * @param int $configId
     * @return JsonResponse
     */
    public function bulkReorder(Request $request, int $configId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'type' => 'required|in:screens,widgets,children',
                'items' => 'required|array',
                'items.*.id' => 'required|integer',
                'items.*.order' => 'required|integer|min:0'
            ]);

            DB::beginTransaction();

            $count = 0;

            foreach ($validated['items'] as $item) {
                if ($validated['type'] === 'screens') {
                    ConfigScreenOverride::where('configuration_id', $configId)
                        ->where('screen_id', $item['id'])
                        ->update(['display_order' => $item['order']]);
                    $count++;
                } elseif ($validated['type'] === 'widgets') {
                    ConfigWidgetOverride::where('configuration_id', $configId)
                        ->where('screen_widget_id', $item['id'])
                        ->update(['display_order' => $item['order'] ?? 0]);
                    $count++;
                } elseif ($validated['type'] === 'children') {
                    ConfigThemeChildOverride::where('id', $item['id'])
                        ->whereHas('configWidgetOverride', function ($q) use ($configId) {
                            $q->where('configuration_id', $configId);
                        })
                        ->update(['display_order' => $item['order']]);
                    $count++;
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Reordered {$count} items",
                'reordered_count' => $count
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to reorder items',
                'error' => $e->getMessage()
            ], 422);
        }
    }
}
