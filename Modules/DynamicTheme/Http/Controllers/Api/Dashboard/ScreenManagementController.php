<?php

namespace Modules\DynamicTheme\Http\Controllers\Api\Dashboard;

use Modules\DynamicTheme\Http\Controllers\Controller;
use Modules\DynamicTheme\Http\Resources\ScreenResource;
use Modules\DynamicTheme\Entities\Screen;
use Modules\DynamicTheme\Entities\ScreenWidget;
use Modules\DynamicTheme\Entities\ScreenWidgetChild;
use Modules\DynamicTheme\Entities\Widget;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScreenManagementController extends Controller
{
    /**
     * Get all screens for dashboard
     */
    public function index(): JsonResponse
    {
        $screens = Screen::with('allowedWidgetsV2')->withCount('screenWidgets')->get();
        return response()->json([
            'success' => true,
            'data' => $screens,
        ]);
    }

    /**
     * Create a new screen
     */
    public function store(Request $request): JsonResponse
    {
                dd($request->all());

        $validated = $request->validate([
            'screen_key' => 'required|string|unique:screens,screen_key|max:100',
            'screen_name' => 'required|string|max:255',
            'min_app_version' => 'nullable|string|max:20',
            'layout' => 'nullable|array',
            'layout.direction' => 'nullable|in:vertical,horizontal',
            'layout.background_color' => 'nullable|string|max:20',
            'layout.background_asset' => 'nullable|string',
        ]);

        $screen = Screen::create([
            'screen_key' => $validated['screen_key'],
            'screen_name' => $validated['screen_name'],
            'min_app_version' => $validated['min_app_version'] ?? '1.0.0',
            'layout' => $validated['layout'] ?? [
                'direction' => 'vertical',
                'background_color' => '#FFFFFF',
                'background_asset' => null,
            ],
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'data' => $screen,
            'message' => 'Screen created successfully',
        ], 201);
    }

    /**
     * Get screen details with widgets
     */
    public function show(int $id): JsonResponse
    {
        $screen = Screen::with([
            'screenWidgets' => fn($q) => $q->orderBy('order'),
            'screenWidgets.widget',
            'screenWidgets.theme',
            'screenWidgets.children' => fn($q) => $q->orderBy('order'),
        ])->find($id);

        if (!$screen) {
            return response()->json([
                'success' => false,
                'message' => 'Screen not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $screen,
        ]);
    }

    /**
     * Update screen
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $screen = Screen::find($id);

        if (!$screen) {
            return response()->json([
                'success' => false,
                'message' => 'Screen not found',
            ], 404);
        }

        $validated = $request->validate([
            'screen_name' => 'sometimes|string|max:255',
            'min_app_version' => 'sometimes|string|max:20',
            'layout' => 'sometimes|array',
            'is_active' => 'sometimes|boolean',
        ]);

        $screen->update($validated);

        return response()->json([
            'success' => true,
            'data' => $screen,
            'message' => 'Screen updated successfully',
        ]);
    }

    /**
     * Delete screen
     */
    public function destroy(int $id): JsonResponse
    {
        $screen = Screen::find($id);

        if (!$screen) {
            return response()->json([
                'success' => false,
                'message' => 'Screen not found',
            ], 404);
        }

        $screen->delete();

        return response()->json([
            'success' => true,
            'message' => 'Screen deleted successfully',
        ]);
    }

    /**
     * Add widget to screen
     */
    public function addWidget(Request $request, int $screenId): JsonResponse
    {
        $screen = Screen::find($screenId);

        if (!$screen) {
            return response()->json([
                'success' => false,
                'message' => 'Screen not found',
            ], 404);
        }

        $validated = $request->validate([
            'widget_id' => 'required|exists:widgets,id',
            'theme_id' => 'nullable|exists:widget_themes,id',
            'order' => 'nullable|integer',
            'is_positioned' => 'nullable|boolean',
            'position' => 'nullable|array',
            'primary_settings' => 'nullable|array',
            'secondary_settings' => 'nullable|array',
            'action' => 'nullable|array',
            'min_app_version' => 'nullable|string|max:20',
        ]);

        $allowedWidgetIds =  $screen->allowedWidgetsV2()->pluck('widget_id')->toArray();
        if (!in_array($validated['widget_id'], $allowedWidgetIds)) {
            return response()->json([
                'success' => false,
                'message' => 'This widget is not allowed on this screen',
            ], 403);
        }

        // $alreadyAdded = $screen->screenWidgets()
        //     ->where('widget_id', $validated['widget_id'])
        //     ->exists();

        // if ($alreadyAdded) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'This widget has already been added',
        //     ], 403);
        // }

        // Get next order if not provided
        if (!isset($validated['order'])) {
            $isPositioned = $validated['is_positioned'] ?? false;
            $maxOrder = $screen->screenWidgets()
                ->where('is_positioned', $isPositioned)
                ->max('order') ?? ($isPositioned ? 99 : 0);
            $validated['order'] = $maxOrder + 1;
        }


        $screenWidget = ScreenWidget::create([
            'screen_id' => $screenId,
            'widget_id' => $validated['widget_id'],
            'theme_id' => $validated['theme_id'] ?? null,
            'order' => $validated['order'],
            'is_positioned' => $validated['is_positioned'] ?? false,
            'position' => $validated['position'] ?? null,
            'primary_settings' => $validated['primary_settings'] ?? [],
            'secondary_settings' => $validated['secondary_settings'] ?? [],
            'action' => $validated['action'] ?? null,
            'min_app_version' => $validated['min_app_version'] ?? '1.0.0',
            'is_active' => true,
        ]);

        $screenWidget->load(['widget', 'theme']);

        return response()->json([
            'success' => true,
            'data' => $screenWidget,
            'message' => 'Widget added to screen successfully',
        ], 201);
    }

    /**
     * Update widget in screen
     */
    public function updateWidget(Request $request, int $screenId, int $widgetId): JsonResponse
    {
        $screenWidget = ScreenWidget::where('screen_id', $screenId)
            ->where('id', $widgetId)
            ->first();

        if (!$screenWidget) {
            return response()->json([
                'success' => false,
                'message' => 'Screen widget not found',
            ], 404);
        }

        $validated = $request->validate([
            'theme_id' => 'sometimes|nullable|exists:widget_themes,id',
            'order' => 'sometimes|integer',
            'is_positioned' => 'sometimes|boolean',
            'position' => 'sometimes|nullable|array',
            'primary_settings' => 'sometimes|array',
            'secondary_settings' => 'sometimes|array',
            'action' => 'sometimes|nullable|array',
            'min_app_version' => 'sometimes|string|max:20',
            'is_active' => 'sometimes|boolean',
        ]);

        $screenWidget->update($validated);
        $screenWidget->load(['widget', 'theme']);

        return response()->json([
            'success' => true,
            'data' => $screenWidget,
            'message' => 'Screen widget updated successfully',
        ]);
    }

    /**
     * Remove widget from screen
     */
    public function removeWidget(int $screenId, int $widgetId): JsonResponse
    {
        $screenWidget = ScreenWidget::where('screen_id', $screenId)
            ->where('id', $widgetId)
            ->first();

        if (!$screenWidget) {
            return response()->json([
                'success' => false,
                'message' => 'Screen widget not found',
            ], 404);
        }

        $screenWidget->delete();

        return response()->json([
            'success' => true,
            'message' => 'Widget removed from screen successfully',
        ]);
    }

    /**
     * Reorder widgets in screen
     */
    public function reorderWidgets(Request $request, int $screenId): JsonResponse
    {
        $screen = Screen::find($screenId);

        if (!$screen) {
            return response()->json([
                'success' => false,
                'message' => 'Screen not found',
            ], 404);
        }

        $validated = $request->validate([
            'widgets' => 'required|array',
            'widgets.*.id' => 'required|exists:screen_widgets,id',
            'widgets.*.order' => 'required|integer',
        ]);

        DB::transaction(function () use ($validated, $screenId) {
            foreach ($validated['widgets'] as $widget) {
                ScreenWidget::where('id', $widget['id'])
                    ->where('screen_id', $screenId)
                    ->update(['order' => $widget['order']]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Widgets reordered successfully',
        ]);
    }

    /**
     * Add child to screen widget
     */
    public function addWidgetChild(Request $request, int $screenId, int $widgetId): JsonResponse
    {
        $screenWidget = ScreenWidget::where('screen_id', $screenId)
            ->where('id', $widgetId)
            ->first();

        if (!$screenWidget) {
            return response()->json([
                'success' => false,
                'message' => 'Screen widget not found',
            ], 404);
        }

        $validated = $request->validate([
            'child_key' => 'required|string|max:100',
            'child_type' => 'required|in:tab,category,special',
            'label' => 'required|string|max:255',
            'order' => 'nullable|integer',
            'is_visible' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'action' => 'nullable|array',
            'assets' => 'nullable|array',
            'position' => 'nullable|string|in:left,right',
        ]);

        // Get next order if not provided
        if (!isset($validated['order'])) {
            $maxOrder = $screenWidget->children()->max('order') ?? 0;
            $validated['order'] = $maxOrder + 1;
        }

        $child = ScreenWidgetChild::create([
            'screen_widget_id' => $widgetId,
            'child_key' => $validated['child_key'],
            'child_type' => $validated['child_type'],
            'label' => $validated['label'],
            'order' => $validated['order'],
            'is_visible' => $validated['is_visible'] ?? true,
            'is_active' => $validated['is_active'] ?? false,
            'action' => $validated['action'] ?? null,
            'assets' => $validated['assets'] ?? null,
            'position' => $validated['position'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'data' => $child,
            'message' => 'Child added successfully',
        ], 201);
    }


    public function addWidgetChildV2(Request $request, int $widgetId): JsonResponse
    {
        $screenWidget = Widget::where('id', $widgetId)
            ->first();

        if (!$screenWidget) {
            return response()->json([
                'success' => false,
                'message' => 'Screen widget not found',
            ], 404);
        }

        $validated = $request->validate([
            'child_key' => 'required|string|max:100',
            'child_type' => 'required|in:tab,category,special',
            'label' => 'required|string|max:255',
            'order' => 'nullable|integer',
            'is_visible' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'action' => 'nullable|array',
            'assets' => 'nullable|array',
            'position' => 'nullable|string|in:left,right',
        ]);

      

        $child = ScreenWidgetChild::create([
            'screen_widget_id' => $widgetId,
            'child_key' => $validated['child_key'],
            'child_type' => $validated['child_type'],
            'label' => $validated['label'],
            'order' =>  0,
            'is_visible' => $validated['is_visible'] ?? true,
            'is_active' => $validated['is_active'] ?? false,
            'action' => $validated['action'] ?? null,
            'assets' => $validated['assets'] ?? null,
            'position' => $validated['position'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'data' => $child,
            'message' => 'Child added successfully',
        ], 201);
    }
    /**
     * Update widget child
     */
    public function updateWidgetChild(Request $request, int $screenId, int $widgetId, int $childId): JsonResponse
    {
        $child = ScreenWidgetChild::where('id', $childId)
            ->whereHas('screenWidget', fn($q) => $q->where('screen_id', $screenId)->where('id', $widgetId))
            ->first();

        if (!$child) {
            return response()->json([
                'success' => false,
                'message' => 'Child not found',
            ], 404);
        }

        $validated = $request->validate([
            'label' => 'sometimes|string|max:255',
            'order' => 'sometimes|integer',
            'is_visible' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'action' => 'sometimes|nullable|array',
            'assets' => 'sometimes|nullable|array',
            'position' => 'sometimes|nullable|string|in:left,right',
        ]);

        $child->update($validated);

        return response()->json([
            'success' => true,
            'data' => $child,
            'message' => 'Child updated successfully',
        ]);
    }

    /**
     * Remove widget child
     */
    public function removeWidgetChild(int $screenId, int $widgetId, int $childId): JsonResponse
    {
        $child = ScreenWidgetChild::where('id', $childId)
            ->whereHas('screenWidget', fn($q) => $q->where('screen_id', $screenId)->where('id', $widgetId))
            ->first();

        if (!$child) {
            return response()->json([
                'success' => false,
                'message' => 'Child not found',
            ], 404);
        }

        $child->delete();

        return response()->json([
            'success' => true,
            'message' => 'Child removed successfully',
        ]);
    }

    /**
     * Duplicate screen
     */
    public function duplicate(int $id): JsonResponse
    {
        $screen = Screen::with([
            'screenWidgets.children'
        ])->find($id);

        if (!$screen) {
            return response()->json([
                'success' => false,
                'message' => 'Screen not found',
            ], 404);
        }

        $newScreen = DB::transaction(function () use ($screen) {
            // Create new screen
            $newScreen = $screen->replicate();
            $newScreen->screen_key = $screen->screen_key . '_copy_' . time();
            $newScreen->screen_name = $screen->screen_name . ' (Copy)';
            $newScreen->save();

            // Duplicate widgets
            foreach ($screen->screenWidgets as $widget) {
                $newWidget = $widget->replicate();
                $newWidget->screen_id = $newScreen->id;
                $newWidget->save();

                // Duplicate children
                foreach ($widget->children as $child) {
                    $newChild = $child->replicate();
                    $newChild->screen_widget_id = $newWidget->id;
                    $newChild->save();
                }
            }

            return $newScreen;
        });

        return response()->json([
            'success' => true,
            'data' => $newScreen,
            'message' => 'Screen duplicated successfully',
        ], 201);
    }
}
