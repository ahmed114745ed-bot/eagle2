<?php

namespace Modules\DynamicTheme\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\DynamicTheme\Entities\ChildCustomizer;
use Modules\DynamicTheme\Entities\ThemeChild;
use Modules\DynamicTheme\Entities\ConfigThemeChildOverride;
use Modules\DynamicTheme\Services\CustomizerService;

class ChildCustomizerController extends Controller
{
    protected CustomizerService $customizerService;

    public function __construct(CustomizerService $customizerService)
    {
        $this->customizerService = $customizerService;
    }

    /**
     * Get all child customizers for a theme child
     */
    public function indexByThemeChild(ThemeChild $themeChild): JsonResponse
    {
        try {
            $customizers = $themeChild->childCustomizers()
                ->active()
                ->orderBy('display_order')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $customizers,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch child customizers: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all child customizers by configuration override
     */
    public function indexByConfigOverride(ConfigThemeChildOverride $override): JsonResponse
    {
        try {
            $customizers = $override->childCustomizers()
                ->active()
                ->orderBy('display_order')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $customizers,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch child customizers: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific child customizer
     */
    public function show(ChildCustomizer $childCustomizer): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $childCustomizer,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch child customizer: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new child customizer
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'theme_child_id' => 'required|exists:theme_children,id',
                'config_theme_child_override_id' => 'nullable|exists:config_theme_child_overrides,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'shape_config' => 'nullable|array',
                'color_config' => 'nullable|array',
                'border_config' => 'nullable|array',
                'shadow_config' => 'nullable|array',
                'typography_config' => 'nullable|array',
                'layout_config' => 'nullable|array',
                'effects_config' => 'nullable|array',
                'animation_config' => 'nullable|array',
                'is_visible' => 'boolean',
                'display_order' => 'integer|min:0',
                'is_active' => 'boolean',
            ]);

            $childCustomizer = ChildCustomizer::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Child customizer created successfully',
                'data' => $childCustomizer,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create child customizer: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a child customizer
     */
    public function update(Request $request, ChildCustomizer $childCustomizer): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'shape_config' => 'nullable|array',
                'color_config' => 'nullable|array',
                'border_config' => 'nullable|array',
                'shadow_config' => 'nullable|array',
                'typography_config' => 'nullable|array',
                'layout_config' => 'nullable|array',
                'effects_config' => 'nullable|array',
                'animation_config' => 'nullable|array',
                'is_visible' => 'boolean',
                'display_order' => 'integer|min:0',
                'is_active' => 'boolean',
            ]);

            $childCustomizer->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Child customizer updated successfully',
                'data' => $childCustomizer,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update child customizer: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a child customizer
     */
    public function destroy(ChildCustomizer $childCustomizer): JsonResponse
    {
        try {
            $childCustomizer->delete();

            return response()->json([
                'success' => true,
                'message' => 'Child customizer deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete child customizer: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate CSS for a child customizer
     */
    public function generateCSS(ChildCustomizer $childCustomizer): JsonResponse
    {
        try {
            $css = $childCustomizer->generateCSS();

            return response()->json([
                'success' => true,
                'data' => [
                    'css' => $css,
                    'tailwind' => $this->generateTailwindClasses($childCustomizer),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate CSS: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clone a child customizer
     */
    public function clone(ChildCustomizer $childCustomizer): JsonResponse
    {
        try {
            $newCustomizer = $childCustomizer->replicate();
            $newCustomizer->name = $childCustomizer->name . ' (Copy)';
            $newCustomizer->save();

            return response()->json([
                'success' => true,
                'message' => 'Child customizer cloned successfully',
                'data' => $newCustomizer,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clone child customizer: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate Tailwind classes for a child customizer
     */
    protected function generateTailwindClasses(ChildCustomizer $customizer): string
    {
        $classes = [];

        // Color classes
        if ($customizer->color_config) {
            if (isset($customizer->color_config['background'])) {
                $classes[] = 'bg-opacity-50';
            }
        }

        // Border classes
        if ($customizer->border_config && isset($customizer->border_config['width'])) {
            $width = $customizer->border_config['width'];
            if ($width > 0) {
                $classes[] = 'border-' . min(8, $width);
            }
        }

        // Shadow classes
        if ($customizer->shadow_config) {
            $classes[] = 'shadow-lg';
        }

        // Visibility
        if (!$customizer->is_visible) {
            $classes[] = 'hidden';
        }

        return implode(' ', $classes);
    }

    /**
     * Save drawing for a child customizer
     */
    public function saveDrawing(Request $request, ChildCustomizer $childCustomizer): JsonResponse
    {
        try {
            $validated = $request->validate([
                'drawing_data' => 'required|string',
                'drawing_metadata' => 'nullable|array',
            ]);

            $updated = CustomizerService::saveDrawing(
                $childCustomizer->id,
                $validated['drawing_data'],
                $validated['drawing_metadata'] ?? []
            );

            return response()->json([
                'success' => true,
                'message' => 'Drawing saved successfully',
                'data' => $updated,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save drawing: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get drawing for a child customizer
     */
    public function getDrawing(ChildCustomizer $childCustomizer): JsonResponse
    {
        try {
            $drawing = CustomizerService::getDrawing($childCustomizer->id);

            return response()->json([
                'success' => true,
                'data' => $drawing,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch drawing: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all drawings for a configuration
     */
    public function getConfigurationDrawings(Request $request): JsonResponse
    {
        try {
            $configId = $request->query('config_id');
            
            if (!$configId) {
                return response()->json([
                    'success' => false,
                    'message' => 'config_id is required',
                ], 400);
            }

            $drawings = CustomizerService::getConfigurationDrawings($configId);

            return response()->json([
                'success' => true,
                'count' => count($drawings),
                'data' => $drawings,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch drawings: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export child with drawing
     */
    public function exportWithDrawing(ChildCustomizer $childCustomizer): JsonResponse
    {
        try {
            $export = CustomizerService::exportChildWithDrawing($childCustomizer->id);

            return response()->json([
                'success' => true,
                'data' => $export,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get children for a specific configuration
     */
    public function getConfigChildren(Request $request): JsonResponse
    {
        try {
            $configId = $request->query('config_id');
            
            if (!$configId) {
                return response()->json([
                    'success' => false,
                    'message' => 'config_id is required',
                ], 400);
            }

            $children = ChildCustomizer::where('config_widget_override_id', $configId)
                ->active()
                ->orderBy('display_order')
                ->get();

            return response()->json([
                'success' => true,
                'count' => $children->count(),
                'data' => $children,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch children: ' . $e->getMessage(),
            ], 500);
        }
    }
}

