<?php

namespace Modules\DynamicTheme\Http\Controllers\Api\Admin;

use Modules\DynamicTheme\Http\Controllers\Controller;
use Modules\DynamicTheme\Entities\Screen;
use Modules\DynamicTheme\Entities\ScreenWidget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminScreenController extends Controller
{
    /**
     * Display a listing of all screens.
     */
    public function index()
    {
        $screens = Screen::with(['widgets.widget', 'widgets.theme'])
            ->orderBy('display_order')
            ->get();

        return response()->json([
            'data' => $screens
        ]);
    }

    /**
     * Store a newly created screen.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'screen_key' => 'required|string|unique:screens,screen_key|max:100',
            'screen_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'display_order' => 'integer',
            'is_active' => 'boolean',
            'max_widgets' => 'nullable',
        ]);

        $screen = Screen::create($validated);

        return response()->json([
            'message' => 'Screen created successfully',
            'data' => $screen
        ], 201);
    }

    /**
     * Display the specified screen.
     */
    public function show(Screen $screen)
    {
        return response()->json([
            'data' => $screen->load(['widgets.widget', 'widgets.theme'])
        ]);
    }

    /**
     * Update the specified screen.
     */
    public function update(Request $request, Screen $screen)
    {
        $validated = $request->validate([
            'screen_key' => 'sometimes|string|unique:screens,screen_key,' . $screen->id . '|max:100',
            'screen_name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'display_order' => 'integer',
            'is_active' => 'boolean',
            'max_widgets' => 'nullable',

        ]);

        $screen->update($validated);

        return response()->json([
            'message' => 'Screen updated successfully',
            'data' => $screen->fresh()->load(['widgets.widget', 'widgets.theme'])
        ]);
    }

    /**
     * Remove the specified screen.
     */
    public function destroy(Screen $screen)
    {
        $screen->delete();

        return response()->json([
            'message' => 'Screen deleted successfully'
        ]);
    }

    /**
     * Update widgets on a screen.
     */
    public function updateWidgets(Request $request, Screen $screen)
    {
        $validated = $request->validate([
            'widgets' => 'required|array',
            'widgets.*.widget_id' => 'required|exists:widgets,id',
            'widgets.*.widget_theme_id' => 'nullable|exists:widget_themes,id',
            'widgets.*.display_order' => 'required|integer',
            'widgets.*.is_active' => 'boolean',
        ]);

        DB::transaction(function () use ($screen, $validated) {
            // Delete existing widgets
            $screen->widgets()->delete();

            // Create new widgets
            foreach ($validated['widgets'] as $widgetData) {
                ScreenWidget::create([
                    'screen_id' => $screen->id,
                    'widget_id' => $widgetData['widget_id'],
                    'widget_theme_id' => $widgetData['widget_theme_id'] ?? null,
                    'display_order' => $widgetData['display_order'],
                    'is_active' => $widgetData['is_active'] ?? true,
                ]);
            }
        });

        return response()->json([
            'message' => 'Screen widgets updated successfully',
            'data' => $screen->fresh()->load(['widgets.widget', 'widgets.theme'])
        ]);
    }

    /**
     * Preview screen API response.
     */
    public function preview(Screen $screen)
    {
        $screen->load([
            'widgets' => function ($query) {
                $query->active()->orderBy('display_order');
            },
            'widgets.widget.settingsDefinitions',
            'widgets.widget.actions',
            'widgets.theme.assets',
            'widgets.children' => function ($query) {
                $query->orderBy('display_order');
            },
            'widgets.children.widget',
            'widgets.children.theme.assets',
        ]);

        $widgets = $screen->widgets->map(function ($screenWidget) {
            return $this->formatWidget($screenWidget);
        });

        return response()->json([
            'screen_key' => $screen->screen_key,
            'screen_name' => $screen->screen_name,
            'widgets' => $widgets,
        ]);
    }

    /**
     * Duplicate a screen.
     */
    public function duplicate(Screen $screen)
    {
        $screen->load(['widgets']);

        $newScreen = $screen->replicate();
        $newScreen->screen_key = $screen->screen_key . '_copy_' . time();
        $newScreen->screen_name = $screen->screen_name . ' (Copy)';
        $newScreen->save();

        foreach ($screen->widgets as $widget) {
            $newWidget = $widget->replicate();
            $newWidget->screen_id = $newScreen->id;
            $newWidget->save();
        }

        return response()->json([
            'message' => 'Screen duplicated successfully',
            'data' => $newScreen->fresh()->load(['widgets.widget', 'widgets.theme'])
        ], 201);
    }

    /**
     * Format widget for API response.
     */
    private function formatWidget($screenWidget)
    {
        $widget = $screenWidget->widget;
        $theme = $screenWidget->theme;

        // Format settings
        $settings = [];
        foreach ($widget->settingsDefinitions as $setting) {
            $value = $screenWidget->settings[$setting->setting_key] ?? $setting->default_value;
            $settings[$setting->setting_category][$setting->setting_key] = $value;
        }

        // Format assets
        $assets = [];
        if ($theme) {
            foreach ($theme->assets as $asset) {
                $assets[$asset->asset_key] = [
                    'type' => $asset->asset_type,
                    'url' => $asset->default_url,
                ];
            }
        }

        // Format actions
        $actions = $widget->actions->map(function ($action) {
            return [
                'type' => $action->action_type,
                'label' => $action->action_label,
                'target_type' => $action->target_type,
                'target' => null,
            ];
        });

        $result = [
            'widget_key' => $widget->widget_key,
            'type' => $widget->widget_type,
            'theme' => $theme?->theme_key,
            'settings' => $settings,
            'assets' => $assets,
            'actions' => $actions,
        ];

        // Add children for tab containers
        if ($widget->supports_children && $screenWidget->children->count() > 0) {
            $result['children'] = $screenWidget->children->map(function ($child) {
                return $this->formatChildWidget($child);
            });
        }

        return $result;
    }

    /**
     * Format child widget.
     */
    private function formatChildWidget($childWidget)
    {
        $widget = $childWidget->widget;
        $theme = $childWidget->theme;

        $assets = [];
        if ($theme) {
            foreach ($theme->assets as $asset) {
                $assets[$asset->asset_key] = [
                    'type' => $asset->asset_type,
                    'url' => $asset->default_url,
                ];
            }
        }

        return [
            'widget_key' => $widget->widget_key,
            'tab_title' => $childWidget->tab_title,
            'type' => $widget->widget_type,
            'theme' => $theme?->theme_key,
            'assets' => $assets,
        ];
    }
}
