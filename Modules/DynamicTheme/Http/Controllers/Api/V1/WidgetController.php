<?php

namespace Modules\DynamicTheme\Http\Controllers\Api\V1;

use Modules\DynamicTheme\Http\Controllers\Controller;
use Modules\DynamicTheme\Http\Resources\WidgetResource;
use Modules\DynamicTheme\Http\Resources\WidgetThemeResource;
use Modules\DynamicTheme\Entities\Widget;
use Modules\DynamicTheme\Entities\WidgetTheme;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WidgetController extends Controller
{
    /**
     * Get all active widgets with their themes and settings
     */
    public function index(): JsonResponse
    {
        $widgets = Widget::active()
            ->with([
                'activeThemes.assets',
                'primarySettings' => fn($q) => $q->where('is_hidden', false)->orderBy('order'),
                'secondarySettings' => fn($q) => $q->where('is_hidden', false)->orderBy('order'),
                'actions' => fn($q) => $q->where('is_active', true),
            ])
            ->get();

        return response()->json([
            'success' => true,
            'data' => WidgetResource::collection($widgets),
        ]);
    }

    /**
     * Get widget by key with themes and settings
     */
    public function show(string $widgetKey): JsonResponse
    {
        $widget = Widget::where('widget_key', $widgetKey)
            ->where('is_active', true)
            ->with([
                'activeThemes.assets',
                'primarySettings' => fn($q) => $q->where('is_hidden', false)->orderBy('order'),
                'secondarySettings' => fn($q) => $q->where('is_hidden', false)->orderBy('order'),
                'actions' => fn($q) => $q->where('is_active', true),
            ])
            ->first();

        if (!$widget) {
            return response()->json([
                'success' => false,
                'message' => 'Widget not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new WidgetResource($widget),
        ]);
    }

    /**
     * Get themes for a specific widget
     */
    public function themes(string $widgetKey): JsonResponse
    {
        $widget = Widget::where('widget_key', $widgetKey)
            ->where('is_active', true)
            ->first();

        if (!$widget) {
            return response()->json([
                'success' => false,
                'message' => 'Widget not found',
            ], 404);
        }

        $themes = $widget->activeThemes()->with('assets')->get();

        return response()->json([
            'success' => true,
            'data' => WidgetThemeResource::collection($themes),
        ]);
    }

    /**
     * Get theme by key with assets
     */
    public function showTheme(string $themeKey): JsonResponse
    {
        $theme = WidgetTheme::where('theme_key', $themeKey)
            ->where('is_active', true)
            ->with(['assets', 'widget'])
            ->first();

        if (!$theme) {
            return response()->json([
                'success' => false,
                'message' => 'Theme not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new WidgetThemeResource($theme),
        ]);
    }


    public function parents()
    {
        $widgets = Widget::whereNull('parent_id')
            ->select('id', 'display_name')
            ->get();

        return response()->json([
            'data' => $widgets
        ]);
    }

}
