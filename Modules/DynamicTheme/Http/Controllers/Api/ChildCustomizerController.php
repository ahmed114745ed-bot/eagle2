<?php

namespace Modules\DynamicTheme\Http\Controllers\Api;

use Modules\DynamicTheme\Http\Controllers\Controller;
use Modules\DynamicTheme\Entities\ChildCustomizer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ChildCustomizerController extends Controller
{
    /**
     * Get all child customizers for a widget override
     */
    public function indexByWidgetOverride(int $configWidgetOverrideId): JsonResponse
    {
        try {
            $customizers = ChildCustomizer::where('config_widget_override_id', $configWidgetOverrideId)
                ->with('themeChild')
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
                'message' => 'Failed to load child customizers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific child customizer
     */
    public function show(int $id): JsonResponse
    {
        try {
            $customizer = ChildCustomizer::with('themeChild')->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'customizer' => $customizer,
                'css' => $customizer->generateCSS()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Child customizer not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Create new child customizer
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'config_widget_override_id' => 'required|integer|exists:config_widget_overrides,id',
                'theme_child_id' => 'nullable|integer|exists:theme_children,id',
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
                'position_config' => 'nullable|array',
                'effects_config' => 'nullable|array',
                'name' => 'nullable|string',
                'description' => 'nullable|string',
            ]);

            $customizer = ChildCustomizer::create($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Child customizer created successfully',
                'customizer' => $customizer
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create child customizer',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Update child customizer
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $customizer = ChildCustomizer::findOrFail($id);

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
                'position_config' => 'nullable|array',
                'effects_config' => 'nullable|array',
                'name' => 'nullable|string',
                'description' => 'nullable|string',
                'is_active' => 'nullable|boolean',
                'order' => 'nullable|integer',
            ]);

            $customizer->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Child customizer updated successfully',
                'customizer' => $customizer
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update child customizer',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Delete child customizer
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $customizer = ChildCustomizer::findOrFail($id);
            $customizer->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Child customizer deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete child customizer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate CSS
     */
    public function generateCSS(int $id): JsonResponse
    {
        try {
            $customizer = ChildCustomizer::findOrFail($id);
            $css = $customizer->generateCSS();

            return response()->json([
                'status' => 'success',
                'css' => $css,
                'selector' => ".child-{$id}",
                'full_css' => ".child-{$id} { {$css} }"
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
     * Batch update children
     */
    public function batchUpdate(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'customizers' => 'required|array',
                'customizers.*.id' => 'required|integer|exists:child_customizers,id',
                'customizers.*.order' => 'required|integer',
                'customizers.*.is_active' => 'nullable|boolean',
            ]);

            foreach ($validated['customizers'] as $data) {
                $customizer = ChildCustomizer::find($data['id']);
                if ($customizer) {
                    $customizer->update([
                        'order' => $data['order'],
                        'is_active' => $data['is_active'] ?? $customizer->is_active
                    ]);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Child customizers updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to batch update child customizers',
                'error' => $e->getMessage()
            ], 422);
        }
    }
}
