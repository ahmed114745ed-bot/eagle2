<?php

namespace Modules\DynamicTheme\Http\Controllers\Api;

use Modules\DynamicTheme\Http\Controllers\Controller;
use Modules\DynamicTheme\Entities\WidgetCustomizer;
use Modules\DynamicTheme\Entities\ColorPreset;
use Modules\DynamicTheme\Entities\WidgetDesignTemplate;
use Modules\DynamicTheme\Entities\ConfigWidgetOverride;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WidgetCustomizerController extends Controller
{
    /**
     * Get all customizers for a widget override
     * 
     * @param int $configWidgetOverrideId
     * @return JsonResponse
     */
    public function indexByWidgetOverride(int $configWidgetOverrideId): JsonResponse
    {
        try {
            $customizers = WidgetCustomizer::where('config_widget_override_id', $configWidgetOverrideId)
                ->orderBy('order')
                ->get();

            return response()->json([
                'status' => 'success',
                'customizers' => $customizers,
                'total' => $customizers->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load customizers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific customizer
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $customizer = WidgetCustomizer::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'customizer' => $customizer,
                'css' => $customizer->generateCSS(),
                'tailwind_classes' => $customizer->generateTailwindClasses()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Customizer not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Create new customizer
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'config_widget_override_id' => 'required|integer|exists:config_widget_overrides,id',
                'shape_config' => 'nullable|array',
                'color_config' => 'nullable|array',
                'gradient_config' => 'nullable|array',
                'border_config' => 'nullable|array',
                'shadow_config' => 'nullable|array',
                'typography_config' => 'nullable|array',
                'layout_config' => 'nullable|array',
                'effects_config' => 'nullable|array',
                'name' => 'nullable|string',
                'description' => 'nullable|string',
            ]);

            $customizer = WidgetCustomizer::create($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Customizer created successfully',
                'customizer' => $customizer
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create customizer',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Update customizer
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $customizer = WidgetCustomizer::findOrFail($id);

            $validated = $request->validate([
                'shape_config' => 'nullable|array',
                'color_config' => 'nullable|array',
                'gradient_config' => 'nullable|array',
                'background_config' => 'nullable|array',
                'border_config' => 'nullable|array',
                'shadow_config' => 'nullable|array',
                'animation_config' => 'nullable|array',
                'transition_config' => 'nullable|array',
                'typography_config' => 'nullable|array',
                'layout_config' => 'nullable|array',
                'effects_config' => 'nullable|array',
                'name' => 'nullable|string',
                'description' => 'nullable|string',
                'is_active' => 'nullable|boolean',
                'order' => 'nullable|integer',
            ]);

            $customizer->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Customizer updated successfully',
                'customizer' => $customizer
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update customizer',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Delete customizer
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $customizer = WidgetCustomizer::findOrFail($id);
            $customizer->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Customizer deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete customizer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate CSS from customizer
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function generateCSS(int $id): JsonResponse
    {
        try {
            $customizer = WidgetCustomizer::findOrFail($id);
            $css = $customizer->generateCSS();

            return response()->json([
                'status' => 'success',
                'css' => $css,
                'selector' => ".widget-{$id}",
                'full_css' => ".widget-{$id} { {$css} }"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate CSS',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clone customizer
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function clone(int $id): JsonResponse
    {
        try {
            $customizer = WidgetCustomizer::findOrFail($id);
            $cloned = $customizer->clone();
            $cloned->name = $customizer->name . ' (Clone)';
            $cloned->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Customizer cloned successfully',
                'customizer' => $cloned
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to clone customizer',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
