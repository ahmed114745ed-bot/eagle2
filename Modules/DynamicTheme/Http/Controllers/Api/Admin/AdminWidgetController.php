<?php

namespace Modules\DynamicTheme\Http\Controllers\Api\Admin;

use Modules\DynamicTheme\Http\Controllers\Controller;
use Modules\DynamicTheme\Entities\Widget;
use Modules\DynamicTheme\Entities\WidgetSettingsDefinition;
use Modules\DynamicTheme\Entities\WidgetAction;
use Illuminate\Http\Request;

class AdminWidgetController extends Controller
{
    /**
     * Display a listing of all widgets.
     */
    public function index()
    {
        $widgets = Widget::withCount(['themes',  'settingsDefinitions as settings_count'])
            ->with(['themes', 'parent','settingsDefinitions', 'actions'])
            ->orderBy('display_name')
            ->get();

        return response()->json([
            'data' => $widgets
        ]);
    }

    /**
     * Store a newly created widget.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'widget_key' => 'required|string|unique:widgets,widget_key|max:100',
            'display_name' => 'required|string|max:255',
            'widget_type' => 'required|string|max:50',
            'description' => 'nullable|string',
            'supports_children' => 'boolean',
            'is_active' => 'boolean',
            'parent_id' => 'nullable',

        ]);

        $widget = Widget::create($validated);

        return response()->json([
            'message' => 'Widget created successfully',
            'data' => $widget->load(['themes', 'settingsDefinitions', 'actions'])
        ], 201);
    }

    /**
     * Display the specified widget.
     */
    public function show(Widget $widget)
    {
        return response()->json([
            'data' => $widget->load(['themes.assets', 'settingsDefinitions', 'actions'])
        ]);
    }

    /**
     * Update the specified widget.
     */
    public function update(Request $request, Widget $widget)
    {
        $validated = $request->validate([
            'widget_key' => 'sometimes|string|unique:widgets,widget_key,' . $widget->id . '|max:100',
            'display_name' => 'sometimes|string|max:255',
            'widget_type' => 'sometimes|string|max:50',
            'description' => 'nullable|string',
            'supports_children' => 'boolean',
            'is_active' => 'boolean',
            'parent_id' => 'nullable',
            'has_pages' => 'boolean',

        ]);

        $widget->update($validated);

        return response()->json([
            'message' => 'Widget updated successfully',
            'data' => $widget->fresh()->load(['themes', 'settingsDefinitions', 'actions'])
        ]);
    }

    /**
     * Remove the specified widget.
     */
    public function destroy(Widget $widget)
    {
        $widget->delete();

        return response()->json([
            'message' => 'Widget deleted successfully'
        ]);
    }

    /**
     * Get settings definitions for a widget.
     */
    public function getSettings(Widget $widget)
    {
        return response()->json([
            'data' => $widget->settingsDefinitions()->orderBy('order')->get()
        ]);
    }

    /**
     * Add a setting definition to a widget.
     */
    public function addSetting(Request $request, Widget $widget)
    {
        $validated = $request->validate([
            'setting_key' => 'required|string|max:100',
            'setting_label' => 'required|string|max:255',
            'setting_type' => 'required|string|max:50',
            'setting_category' => 'required|in:primary,secondary',
            'default_value' => 'nullable|string',
            'options' => 'nullable|json',
            'validation_rules' => 'nullable|json',
            'is_hidden' => 'boolean',
            'order' => 'integer',
        ]);

        $validated['widget_id'] = $widget->id;
        $setting = WidgetSettingsDefinition::create($validated);

        return response()->json([
            'message' => 'Setting added successfully',
            'data' => $setting
        ], 201);
    }

    /**
     * Update a setting definition.
     */
    public function updateSetting(Request $request, Widget $widget, WidgetSettingsDefinition $setting)
    {
        $validated = $request->validate([
            'setting_key' => 'sometimes|string|max:100',
            'setting_label' => 'sometimes|string|max:255',
            'setting_type' => 'sometimes|string|max:50',
            'setting_category' => 'sometimes|in:primary,secondary',
            'default_value' => 'nullable|string',
            'options' => 'nullable|json',
            'validation_rules' => 'nullable|json',
            'is_hidden' => 'boolean',
            'order' => 'integer',
        ]);

        $setting->update($validated);

        return response()->json([
            'message' => 'Setting updated successfully',
            'data' => $setting
        ]);
    }

    /**
     * Delete a setting definition.
     */
    public function deleteSetting(Widget $widget, WidgetSettingsDefinition $setting)
    {
        $setting->delete();

        return response()->json([
            'message' => 'Setting deleted successfully'
        ]);
    }

    /**
     * Get actions for a widget.
     */
    public function getActions(Widget $widget)
    {
        return response()->json([
            // Some legacy schemas may not have an 'order' column; fall back to ID ordering
            'data' => $widget->actions()->orderBy('id')->get()
        ]);
    }

    /**
     * Add an action to a widget.
     */
    public function addAction(Request $request, Widget $widget)
    {
        $validated = $request->validate([
            'action_type' => 'required|in:screen,webview,filter,internal',
            'action_label' => 'required|string|max:255',
            'target_type' => 'required|string|max:50',
            'requires_target' => 'boolean',
            'order' => 'integer',
        ]);

        $validated['widget_id'] = $widget->id;
        $action = WidgetAction::create($validated);

        return response()->json([
            'message' => 'Action added successfully',
            'data' => $action
        ], 201);
    }

    /**
     * Update an action.
     */
    public function updateAction(Request $request, Widget $widget, WidgetAction $action)
    {
        $validated = $request->validate([
            'action_type' => 'sometimes|in:screen,webview,filter,internal',
            'action_label' => 'sometimes|string|max:255',
            'target_type' => 'sometimes|string|max:50',
            'requires_target' => 'boolean',
            'order' => 'integer',
        ]);

        $action->update($validated);

        return response()->json([
            'message' => 'Action updated successfully',
            'data' => $action
        ]);
    }

    /**
     * Delete an action.
     */
    public function deleteAction(Widget $widget, WidgetAction $action)
    {
        $action->delete();

        return response()->json([
            'message' => 'Action deleted successfully'
        ]);
    }
}
