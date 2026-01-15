<?php

namespace Modules\DynamicTheme\Http\Controllers\Api;

use Modules\DynamicTheme\Http\Controllers\Controller;
use Modules\DynamicTheme\Entities\ConfigWidgetOverride;
use Modules\DynamicTheme\Entities\WidgetCustomizer;
use Modules\DynamicTheme\Entities\ChildCustomizer;
use Modules\DynamicTheme\Entities\ColorPreset;
use Modules\DynamicTheme\Entities\WidgetDesignTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UnifiedCustomizerEndpointController extends Controller
{
    /**
     * Get complete widget customization with all children, presets, and templates
     * This is the main unified endpoint that returns everything at once
     */
    public function getComplete(int $configId, int $widgetOverrideId): JsonResponse
    {
        try {
            // Get widget customizer
            $widgetCustomizer = WidgetCustomizer::where('config_widget_override_id', $widgetOverrideId)->first();

            if (!$widgetCustomizer) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Widget customizer not found'
                ], 404);
            }

            // Get all child customizers
            $childCustomizers = ChildCustomizer::where('config_widget_override_id', $widgetOverrideId)
                ->orderBy('order')
                ->get();

            // Get color presets for this configuration
            $colorPresets = ColorPreset::where('configuration_id', $configId)
                ->orderBy('is_default', 'desc')
                ->get();

            // Get design templates
            $designTemplates = WidgetDesignTemplate::where('configuration_id', $configId)
                ->orderBy('created_at', 'desc')
                ->get();

            // Generate complete CSS
            $css = $this->generateCompleteCss($widgetCustomizer, $childCustomizers);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'widget' => [
                        'id' => $widgetCustomizer->id,
                        'config_widget_override_id' => $widgetCustomizer->config_widget_override_id,
                        'customizer' => $widgetCustomizer,
                        'css' => $widgetCustomizer->generateCSS(),
                        'tailwind_classes' => $widgetCustomizer->generateTailwindClasses() ?? []
                    ],
                    'children' => $childCustomizers->map(function ($child) {
                        return [
                            'id' => $child->id,
                            'name' => $child->name,
                            'description' => $child->description,
                            'is_active' => $child->is_active,
                            'order' => $child->order,
                            'customizer' => $child,
                            'css' => $child->generateCSS()
                        ];
                    }),
                    'color_presets' => $colorPresets,
                    'design_templates' => $designTemplates,
                    'compiled_css' => $css,
                    'summary' => [
                        'total_children' => $childCustomizers->count(),
                        'active_children' => $childCustomizers->where('is_active', true)->count(),
                        'total_presets' => $colorPresets->count(),
                        'total_templates' => $designTemplates->count()
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve complete customization',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save complete configuration (widget + all children at once)
     */
    public function saveComplete(Request $request, int $configId, int $widgetOverrideId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'widget' => 'required|array',
                'children' => 'nullable|array',
                'color_presets' => 'nullable|array',
                'design_template' => 'nullable|array',
            ]);

            // Update widget customizer
            $widgetCustomizer = WidgetCustomizer::where('config_widget_override_id', $widgetOverrideId)
                ->firstOrCreate(
                    ['config_widget_override_id' => $widgetOverrideId],
                    ['name' => 'Default Widget Customizer']
                );

            $widgetCustomizer->update($validated['widget']);

            // Batch update/create children
            $savedChildren = [];
            if (isset($validated['children']) && is_array($validated['children'])) {
                foreach ($validated['children'] as $childData) {
                    if (isset($childData['id'])) {
                        $child = ChildCustomizer::find($childData['id']);
                        if ($child) {
                            $child->update($childData);
                            $savedChildren[] = $child;
                        }
                    } else {
                        $childData['config_widget_override_id'] = $widgetOverrideId;
                        $savedChildren[] = ChildCustomizer::create($childData);
                    }
                }
            }

            // Save color presets if provided
            $savedPresets = [];
            if (isset($validated['color_presets']) && is_array($validated['color_presets'])) {
                foreach ($validated['color_presets'] as $presetData) {
                    $presetData['configuration_id'] = $configId;
                    if (isset($presetData['id'])) {
                        $preset = ColorPreset::find($presetData['id']);
                        if ($preset) {
                            $preset->update($presetData);
                            $savedPresets[] = $preset;
                        }
                    } else {
                        $savedPresets[] = ColorPreset::create($presetData);
                    }
                }
            }

            // Save design template if provided
            $savedTemplate = null;
            if (isset($validated['design_template'])) {
                $templateData = $validated['design_template'];
                $templateData['configuration_id'] = $configId;
                if (isset($templateData['id'])) {
                    $template = WidgetDesignTemplate::find($templateData['id']);
                    if ($template) {
                        $template->update($templateData);
                        $savedTemplate = $template;
                    }
                } else {
                    $savedTemplate = WidgetDesignTemplate::create($templateData);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Complete configuration saved successfully',
                'data' => [
                    'widget' => $widgetCustomizer,
                    'children' => $savedChildren,
                    'color_presets' => $savedPresets,
                    'design_template' => $savedTemplate
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save complete configuration',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Export complete configuration as JSON
     */
    public function exportComplete(int $configId, int $widgetOverrideId): JsonResponse
    {
        try {
            $widgetCustomizer = WidgetCustomizer::where('config_widget_override_id', $widgetOverrideId)->first();

            if (!$widgetCustomizer) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Widget customizer not found'
                ], 404);
            }

            $childCustomizers = ChildCustomizer::where('config_widget_override_id', $widgetOverrideId)->get();
            $colorPresets = ColorPreset::where('configuration_id', $configId)->get();
            $designTemplates = WidgetDesignTemplate::where('configuration_id', $configId)->get();

            $exportData = [
                'export_date' => now()->toIso8601String(),
                'version' => '1.0',
                'configuration_id' => $configId,
                'widget_override_id' => $widgetOverrideId,
                'widget' => $widgetCustomizer->toArray(),
                'children' => $childCustomizers->toArray(),
                'color_presets' => $colorPresets->toArray(),
                'design_templates' => $designTemplates->toArray(),
            ];

            return response()->json([
                'status' => 'success',
                'data' => $exportData,
                'filename' => "widget-customization-{$configId}-{$widgetOverrideId}.json"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to export configuration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import complete configuration from JSON
     */
    public function importComplete(Request $request, int $configId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'configuration' => 'required|array',
                'override_existing' => 'boolean'
            ]);

            $config = $validated['configuration'];
            $overrideExisting = $validated['override_existing'] ?? true;

            // Handle widget customizer
            $widgetCustomizer = null;
            if (isset($config['widget'])) {
                $widgetOverrideId = $config['widget']['config_widget_override_id'] ?? null;
                if ($widgetOverrideId) {
                    $widgetCustomizer = WidgetCustomizer::where('config_widget_override_id', $widgetOverrideId)->first();
                    
                    if ($widgetCustomizer && $overrideExisting) {
                        $widgetCustomizer->update($config['widget']);
                    } elseif (!$widgetCustomizer) {
                        $widgetCustomizer = WidgetCustomizer::create($config['widget']);
                    }
                }
            }

            // Handle children
            $importedChildren = [];
            if (isset($config['children']) && is_array($config['children'])) {
                foreach ($config['children'] as $childData) {
                    if ($overrideExisting && isset($childData['id'])) {
                        $child = ChildCustomizer::find($childData['id']);
                        if ($child) {
                            $child->update($childData);
                            $importedChildren[] = $child;
                        }
                    } else {
                        unset($childData['id']);
                        $importedChildren[] = ChildCustomizer::create($childData);
                    }
                }
            }

            // Handle color presets
            $importedPresets = [];
            if (isset($config['color_presets']) && is_array($config['color_presets'])) {
                foreach ($config['color_presets'] as $presetData) {
                    $presetData['configuration_id'] = $configId;
                    if ($overrideExisting && isset($presetData['id'])) {
                        $preset = ColorPreset::find($presetData['id']);
                        if ($preset) {
                            $preset->update($presetData);
                            $importedPresets[] = $preset;
                        }
                    } else {
                        unset($presetData['id']);
                        $importedPresets[] = ColorPreset::create($presetData);
                    }
                }
            }

            // Handle design templates
            $importedTemplates = [];
            if (isset($config['design_templates']) && is_array($config['design_templates'])) {
                foreach ($config['design_templates'] as $templateData) {
                    $templateData['configuration_id'] = $configId;
                    if ($overrideExisting && isset($templateData['id'])) {
                        $template = WidgetDesignTemplate::find($templateData['id']);
                        if ($template) {
                            $template->update($templateData);
                            $importedTemplates[] = $template;
                        }
                    } else {
                        unset($templateData['id']);
                        $importedTemplates[] = WidgetDesignTemplate::create($templateData);
                    }
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Configuration imported successfully',
                'data' => [
                    'widget' => $widgetCustomizer,
                    'children' => $importedChildren,
                    'color_presets' => $importedPresets,
                    'design_templates' => $importedTemplates,
                    'summary' => [
                        'widget_imported' => !is_null($widgetCustomizer),
                        'children_imported' => count($importedChildren),
                        'presets_imported' => count($importedPresets),
                        'templates_imported' => count($importedTemplates)
                    ]
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to import configuration',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Clone complete configuration to another widget
     */
    public function cloneComplete(Request $request, int $sourceWidgetOverrideId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'target_widget_override_id' => 'required|integer|exists:config_widget_overrides,id'
            ]);

            $sourceWidget = WidgetCustomizer::where('config_widget_override_id', $sourceWidgetOverrideId)->first();

            if (!$sourceWidget) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Source widget customizer not found'
                ], 404);
            }

            // Clone widget
            $clonedWidget = $sourceWidget->replicate();
            $clonedWidget->config_widget_override_id = $validated['target_widget_override_id'];
            $clonedWidget->save();

            // Clone children
            $clonedChildren = [];
            $children = ChildCustomizer::where('config_widget_override_id', $sourceWidgetOverrideId)->get();
            foreach ($children as $child) {
                $clonedChild = $child->replicate();
                $clonedChild->config_widget_override_id = $validated['target_widget_override_id'];
                $clonedChild->save();
                $clonedChildren[] = $clonedChild;
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Configuration cloned successfully',
                'data' => [
                    'widget' => $clonedWidget,
                    'children' => $clonedChildren,
                    'summary' => [
                        'widget_cloned' => true,
                        'children_cloned' => count($clonedChildren)
                    ]
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to clone configuration',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Generate complete CSS from widget and all children
     */
    private function generateCompleteCss(WidgetCustomizer $widget, $children): string
    {
        $css = "/* Widget Customization CSS */\n";
        $css .= ".widget-{$widget->id} {\n";
        $css .= $widget->generateCSS();
        $css .= "\n}\n\n";

        if ($children && $children->count() > 0) {
            $css .= "/* Children Customization */\n";
            foreach ($children as $child) {
                if ($child->is_active) {
                    $css .= ".child-{$child->id} {\n";
                    $css .= $child->generateCSS();
                    $css .= "\n}\n\n";
                }
            }
        }

        return $css;
    }
}
