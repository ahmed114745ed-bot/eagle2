<?php

namespace Modules\DynamicTheme\Http\Controllers\Api\Dashboard;

use Modules\DynamicTheme\Http\Controllers\Controller;
use Modules\DynamicTheme\Http\Resources\WidgetResource;
use Modules\DynamicTheme\Http\Resources\WidgetThemeResource;
use Modules\DynamicTheme\Entities\Widget;
use Modules\DynamicTheme\Entities\WidgetTheme;
use Modules\DynamicTheme\Entities\ThemeAsset;
use Modules\DynamicTheme\Entities\WidgetSettingsDefinition;
use Modules\DynamicTheme\Entities\WidgetAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WidgetManagementController extends Controller
{
    /**
     * Get all widgets for dashboard
     */
    public function index(): JsonResponse
    {
        
        $widgets = Widget::with([
            'activeThemes',
            'primarySettings' => fn($q) => $q->orderBy('order'),
            'secondarySettings' => fn($q) => $q->orderBy('order'),
            'actions',
            'parent',
        ])->get();

        return response()->json([
            'success' => true,
            'data' => WidgetResource::collection($widgets),
        ]);
    }

    /**
     * Create a new widget
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'widget_type' => 'required|string|max:100',
            'widget_key' => 'required|string|unique:widgets,widget_key|max:100',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_repeatable' => 'nullable|boolean',
            'has_children' => 'nullable|boolean',
            'parent_id' => 'nullable',
            'min_app_version' => 'nullable|string|max:20',
            'icon' => 'nullable|string|max:100',
        ]);

        $widget = Widget::create([
            'widget_type' => $validated['widget_type'],
            'widget_key' => $validated['widget_key'],
            'display_name' => $validated['display_name'],
            'description' => $validated['description'] ?? null,
            'is_repeatable' => $validated['is_repeatable'] ?? false,
            'has_children' => $validated['has_children'] ?? false,
            'parent_id' => $validated['parent_id'] ?? null,
            'min_app_version' => $validated['min_app_version'] ?? '1.0.0',
            'icon' => $validated['icon'] ?? null,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'data' => $widget,
            'message' => 'Widget created successfully',
        ], 201);
    }

    /**
     * Get widget by ID
     */
    public function show(int $id): JsonResponse
    {
        $widget = Widget::with([
            'themes.assets',
            'settingsDefinitions' => fn($q) => $q->orderBy('order'),
            'actions',
        ])->find($id);

        if (!$widget) {
            return response()->json([
                'success' => false,
                'message' => 'Widget not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $widget,
        ]);
    }

    /**
     * Update widget
     */
    public function update(Request $request, int $id): JsonResponse
    {
           
        $widget = Widget::find($id);

        if (!$widget) {
            return response()->json([
                'success' => false,
                'message' => 'Widget not found',
            ], 404);
        }

        $validated = $request->validate([
            'display_name' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'is_repeatable' => 'sometimes|boolean',
            'has_children' => 'sometimes|boolean',
            'parent_id' => 'nullable',
            'min_app_version' => 'sometimes|string|max:20',
            'icon' => 'sometimes|nullable|string|max:100',
            'is_active' => 'sometimes|boolean',
        ]);

        $widget->update($validated);

        return response()->json([
            'success' => true,
            'data' => $widget,
            'message' => 'Widget updated successfully',
        ]);
    }

    /**
     * Delete widget
     */
    public function destroy(int $id): JsonResponse
    {
        $widget = Widget::find($id);

        if (!$widget) {
            return response()->json([
                'success' => false,
                'message' => 'Widget not found',
            ], 404);
        }

        // Check if widget is used in any screen
        $usageCount = $widget->screenWidgets()->count();
        if ($usageCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete widget. It's used in {$usageCount} screen(s).",
            ], 400);
        }

        $widget->delete();

        return response()->json([
            'success' => true,
            'message' => 'Widget deleted successfully',
        ]);
    }

    /**
     * Get themes for a widget
     */
    public function getThemes(int $widgetId): JsonResponse
    {
        $widget = Widget::find($widgetId);

        if (!$widget) {
            return response()->json([
                'success' => false,
                'message' => 'Widget not found',
            ], 404);
        }

        $themes = WidgetTheme::where('widget_id', $widgetId)
            ->with('assets')
            ->get();

        return response()->json([
            'success' => true,
            'data' => WidgetThemeResource::collection($themes),
        ]);
    }

    /**
     * Add theme to widget
     */
    public function addTheme(Request $request, int $widgetId): JsonResponse
    {
        $widget = Widget::find($widgetId);

        if (!$widget) {
            return response()->json([
                'success' => false,
                'message' => 'Widget not found',
            ], 404);
        }

        $validated = $request->validate([
            'theme_key' => 'required|string|unique:widget_themes,theme_key|max:100',
            'theme_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'preview_image' => 'nullable|string',
            'is_default' => 'nullable|boolean',
            'assets' => 'nullable|array',
            'assets.*.asset_key' => 'required|string|max:100',
            'assets.*.asset_label' => 'required|string|max:255',
            'assets.*.asset_type' => 'required|in:image,svga,vap,alpha',
            'assets.*.default_url' => 'nullable|string',
            'assets.*.is_required' => 'nullable|boolean',
        ]);

        $theme = DB::transaction(function () use ($validated, $widgetId, $widget) {
            // If this is default, remove default from other themes
            if ($validated['is_default'] ?? false) {
                $widget->themes()->update(['is_default' => false]);
            }

            $theme = WidgetTheme::create([
                'widget_id' => $widgetId,
                'theme_key' => $validated['theme_key'],
                'theme_name' => $validated['theme_name'],
                'description' => $validated['description'] ?? null,
                'preview_image' => $validated['preview_image'] ?? null,
                'is_default' => $validated['is_default'] ?? false,
                'is_active' => true,
            ]);

            // Create assets
            if (!empty($validated['assets'])) {
                $order = 1;
                foreach ($validated['assets'] as $asset) {
                    ThemeAsset::create([
                        'theme_id' => $theme->id,
                        'asset_key' => $asset['asset_key'],
                        'asset_label' => $asset['asset_label'],
                        'asset_type' => $asset['asset_type'],
                        'default_url' => $asset['default_url'] ?? null,
                        'is_required' => $asset['is_required'] ?? false,
                        'order' => $order++,
                    ]);
                }
            }

            return $theme;
        });

        $theme->load('assets');

        return response()->json([
            'success' => true,
            'data' => new WidgetThemeResource($theme),
            'message' => 'Theme added successfully',
        ], 201);
    }

    /**
     * Update theme
     */
    public function updateTheme(Request $request, int $widgetId, int $themeId): JsonResponse
    {
        $theme = WidgetTheme::where('widget_id', $widgetId)
            ->where('id', $themeId)
            ->first();

        if (!$theme) {
            return response()->json([
                'success' => false,
                'message' => 'Theme not found',
            ], 404);
        }

        $validated = $request->validate([
            'theme_name' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'preview_image' => 'sometimes|nullable|string',
            'is_default' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ]);

        // If setting as default, remove default from others
        if (($validated['is_default'] ?? false) && !$theme->is_default) {
            WidgetTheme::where('widget_id', $widgetId)
                ->where('id', '!=', $themeId)
                ->update(['is_default' => false]);
        }

        $theme->update($validated);

        return response()->json([
            'success' => true,
            'data' => new WidgetThemeResource($theme),
            'message' => 'Theme updated successfully',
        ]);
    }

    /**
     * Delete theme
     */
    public function deleteTheme(int $widgetId, int $themeId): JsonResponse
    {
        $theme = WidgetTheme::where('widget_id', $widgetId)
            ->where('id', $themeId)
            ->first();

        if (!$theme) {
            return response()->json([
                'success' => false,
                'message' => 'Theme not found',
            ], 404);
        }

        // Check if theme is used
        $usageCount = $theme->screenWidgets()->count();
        if ($usageCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete theme. It's used in {$usageCount} screen widget(s).",
            ], 400);
        }

        $theme->delete();

        return response()->json([
            'success' => true,
            'message' => 'Theme deleted successfully',
        ]);
    }

    /**
     * Add setting definition to widget
     */
    public function addSetting(Request $request, int $widgetId): JsonResponse
    {
        $widget = Widget::find($widgetId);

        if (!$widget) {
            return response()->json([
                'success' => false,
                'message' => 'Widget not found',
            ], 404);
        }

        $validated = $request->validate([
            'setting_key' => 'required|string|max:100',
            'setting_label' => 'required|string|max:255',
            'setting_type' => 'required|in:text,number,boolean,select,color,json,range',
            'setting_category' => 'required|in:primary,secondary',
            'default_value' => 'nullable|string',
            'options' => 'nullable|array',
            'validation_rules' => 'nullable|array',
            'is_hidden' => 'nullable|boolean',
            'order' => 'nullable|integer',
        ]);

        // Check if setting key already exists
        $exists = $widget->settingsDefinitions()
            ->where('setting_key', $validated['setting_key'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Setting key already exists for this widget',
            ], 400);
        }

        // Get next order if not provided
        if (!isset($validated['order'])) {
            $maxOrder = $widget->settingsDefinitions()
                ->where('setting_category', $validated['setting_category'])
                ->max('order') ?? 0;
            $validated['order'] = $maxOrder + 1;
        }

        $setting = WidgetSettingsDefinition::create([
            'widget_id' => $widgetId,
            ...$validated,
        ]);

        return response()->json([
            'success' => true,
            'data' => $setting,
            'message' => 'Setting added successfully',
        ], 201);
    }

    /**
     * Update setting definition
     */
    public function updateSetting(Request $request, int $widgetId, int $settingId): JsonResponse
    {
        $setting = WidgetSettingsDefinition::where('widget_id', $widgetId)
            ->where('id', $settingId)
            ->first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Setting not found',
            ], 404);
        }

        $validated = $request->validate([
            'setting_label' => 'sometimes|string|max:255',
            'setting_type' => 'sometimes|in:text,number,boolean,select,color,json,range',
            'default_value' => 'sometimes|nullable|string',
            'options' => 'sometimes|nullable|array',
            'validation_rules' => 'sometimes|nullable|array',
            'is_hidden' => 'sometimes|boolean',
            'order' => 'sometimes|integer',
        ]);

        $setting->update($validated);

        return response()->json([
            'success' => true,
            'data' => $setting,
            'message' => 'Setting updated successfully',
        ]);
    }

    /**
     * Delete setting definition
     */
    public function deleteSetting(int $widgetId, int $settingId): JsonResponse
    {
        $setting = WidgetSettingsDefinition::where('widget_id', $widgetId)
            ->where('id', $settingId)
            ->first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Setting not found',
            ], 404);
        }

        $setting->delete();

        return response()->json([
            'success' => true,
            'message' => 'Setting deleted successfully',
        ]);
    }

    /**
     * Add action to widget
     */
    public function addAction(Request $request, int $widgetId): JsonResponse
    {
        $widget = Widget::find($widgetId);

        if (!$widget) {
            return response()->json([
                'success' => false,
                'message' => 'Widget not found',
            ], 404);
        }

        $validated = $request->validate([
            'action_type' => 'required|in:screen,webview,filter,internal',
            'action_label' => 'required|string|max:255',
            'requires_target' => 'nullable|boolean',
            'target_type' => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ]);

        // Check if action type already exists
        $exists = $widget->actions()
            ->where('action_type', $validated['action_type'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Action type already exists for this widget',
            ], 400);
        }

        $action = WidgetAction::create([
            'widget_id' => $widgetId,
            'action_type' => $validated['action_type'],
            'action_label' => $validated['action_label'],
            'requires_target' => $validated['requires_target'] ?? true,
            'target_type' => $validated['target_type'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'data' => $action,
            'message' => 'Action added successfully',
        ], 201);
    }

    /**
     * Delete action
     */
    public function deleteAction(int $widgetId, int $actionId): JsonResponse
    {
        $action = WidgetAction::where('widget_id', $widgetId)
            ->where('id', $actionId)
            ->first();

        if (!$action) {
            return response()->json([
                'success' => false,
                'message' => 'Action not found',
            ], 404);
        }

        $action->delete();

        return response()->json([
            'success' => true,
            'message' => 'Action deleted successfully',
        ]);
    }
}
