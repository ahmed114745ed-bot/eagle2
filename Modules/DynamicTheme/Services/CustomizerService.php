<?php

namespace Modules\DynamicTheme\Services;

use Modules\DynamicTheme\Entities\WidgetCustomizer;
use Modules\DynamicTheme\Entities\ColorPreset;
use Modules\DynamicTheme\Entities\WidgetDesignTemplate;
use Modules\DynamicTheme\Entities\ChildCustomizer;
use Modules\DynamicTheme\Entities\ConfigWidgetOverride;

class CustomizerService
{
    /**
     * Create customizer with design configuration
     */
    public static function createCustomizer(
        int $configWidgetOverrideId,
        array $designConfig,
        string $name = null
    ): WidgetCustomizer {
        return WidgetCustomizer::create([
            'config_widget_override_id' => $configWidgetOverrideId,
            'shape_config' => $designConfig['shape_config'] ?? null,
            'color_config' => $designConfig['color_config'] ?? null,
            'gradient_config' => $designConfig['gradient_config'] ?? null,
            'background_config' => $designConfig['background_config'] ?? null,
            'border_config' => $designConfig['border_config'] ?? null,
            'shadow_config' => $designConfig['shadow_config'] ?? null,
            'animation_config' => $designConfig['animation_config'] ?? null,
            'transition_config' => $designConfig['transition_config'] ?? null,
            'typography_config' => $designConfig['typography_config'] ?? null,
            'layout_config' => $designConfig['layout_config'] ?? null,
            'effects_config' => $designConfig['effects_config'] ?? null,
            'name' => $name,
            'is_active' => true,
        ]);
    }

    /**
     * Update customizer design
     */
    public static function updateCustomizerDesign(
        int $customizerId,
        array $designConfig
    ): WidgetCustomizer {
        $customizer = WidgetCustomizer::findOrFail($customizerId);
        
        $customizer->update([
            'shape_config' => $designConfig['shape_config'] ?? $customizer->shape_config,
            'color_config' => $designConfig['color_config'] ?? $customizer->color_config,
            'gradient_config' => $designConfig['gradient_config'] ?? $customizer->gradient_config,
            'background_config' => $designConfig['background_config'] ?? $customizer->background_config,
            'border_config' => $designConfig['border_config'] ?? $customizer->border_config,
            'shadow_config' => $designConfig['shadow_config'] ?? $customizer->shadow_config,
            'animation_config' => $designConfig['animation_config'] ?? $customizer->animation_config,
            'transition_config' => $designConfig['transition_config'] ?? $customizer->transition_config,
            'typography_config' => $designConfig['typography_config'] ?? $customizer->typography_config,
            'layout_config' => $designConfig['layout_config'] ?? $customizer->layout_config,
            'effects_config' => $designConfig['effects_config'] ?? $customizer->effects_config,
        ]);

        return $customizer;
    }

    /**
     * Get customizer CSS with selector
     */
    public static function getCustomizerCSS(int $customizerId): string
    {
        $customizer = WidgetCustomizer::findOrFail($customizerId);
        return ".widget-customizer-{$customizerId} { " . $customizer->generateCSS() . " }";
    }

    /**
     * Generate all CSS for a widget override
     */
    public static function generateWidgetOverrideCSS(int $configWidgetOverrideId): string
    {
        $customizers = WidgetCustomizer::where('config_widget_override_id', $configWidgetOverrideId)
            ->where('is_active', true)
            ->get();

        $css = '';
        foreach ($customizers as $customizer) {
            $css .= self::getCustomizerCSS($customizer->id) . "\n";
        }

        return $css;
    }

    /**
     * Apply color preset to customizer
     */
    public static function applyColorPreset(int $customizerId, int $presetId): WidgetCustomizer
    {
        $customizer = WidgetCustomizer::findOrFail($customizerId);
        $preset = ColorPreset::findOrFail($presetId);

        $customizer->update([
            'color_config' => $preset->colors,
        ]);

        return $customizer;
    }

    /**
     * Apply design template to widget override
     */
    public static function applyDesignTemplate(
        int $configWidgetOverrideId,
        int $templateId
    ): WidgetCustomizer {
        $template = WidgetDesignTemplate::findOrFail($templateId);

        return self::createCustomizer(
            $configWidgetOverrideId,
            $template->design_config,
            $template->name
        );
    }

    /**
     * Batch apply design template to multiple widget overrides
     */
    public static function batchApplyTemplate(array $widgetOverrideIds, int $templateId): array
    {
        $customizers = [];
        
        foreach ($widgetOverrideIds as $overrideId) {
            $customizers[] = self::applyDesignTemplate($overrideId, $templateId);
        }

        return $customizers;
    }

    /**
     * Convert design config to CSS variables
     */
    public static function configToCSSVariables(array $designConfig): string
    {
        $vars = ':root {';

        if (isset($designConfig['color_config']) && is_array($designConfig['color_config'])) {
            foreach ($designConfig['color_config'] as $key => $value) {
                $vars .= "--color-{$key}: {$value};";
            }
        }

        if (isset($designConfig['typography_config']) && is_array($designConfig['typography_config'])) {
            if (isset($designConfig['typography_config']['fontSize'])) {
                $vars .= "--font-size: {$designConfig['typography_config']['fontSize']}px;";
            }
        }

        if (isset($designConfig['layout_config']) && is_array($designConfig['layout_config'])) {
            if (isset($designConfig['layout_config']['padding'])) {
                $vars .= "--spacing: {$designConfig['layout_config']['padding']}px;";
            }
        }

        $vars .= '}';
        return $vars;
    }

    /**
     * Export customizer design
     */
    public static function exportDesign(int $customizerId): array
    {
        $customizer = WidgetCustomizer::findOrFail($customizerId);

        return [
            'name' => $customizer->name,
            'description' => $customizer->description,
            'design_config' => [
                'shape_config' => $customizer->shape_config,
                'color_config' => $customizer->color_config,
                'gradient_config' => $customizer->gradient_config,
                'background_config' => $customizer->background_config,
                'border_config' => $customizer->border_config,
                'shadow_config' => $customizer->shadow_config,
                'animation_config' => $customizer->animation_config,
                'transition_config' => $customizer->transition_config,
                'typography_config' => $customizer->typography_config,
                'layout_config' => $customizer->layout_config,
                'effects_config' => $customizer->effects_config,
            ],
        ];
    }

    /**
     * Import design from export
     */
    public static function importDesign(int $configWidgetOverrideId, array $designData): WidgetCustomizer
    {
        return self::createCustomizer(
            $configWidgetOverrideId,
            $designData['design_config'],
            $designData['name']
        );
    }

    // ======================== CHILD CUSTOMIZER METHODS ========================

    /**
     * Create a child customizer
     */
    public static function createChildCustomizer(
        int $themeChildId,
        array $designConfig,
        ?int $configThemeChildOverrideId = null
    ): ChildCustomizer {
        return ChildCustomizer::create([
            'theme_child_id' => $themeChildId,
            'config_theme_child_override_id' => $configThemeChildOverrideId,
            'shape_config' => $designConfig['shape_config'] ?? [],
            'color_config' => $designConfig['color_config'] ?? [],
            'border_config' => $designConfig['border_config'] ?? [],
            'shadow_config' => $designConfig['shadow_config'] ?? [],
            'typography_config' => $designConfig['typography_config'] ?? [],
            'layout_config' => $designConfig['layout_config'] ?? [],
            'effects_config' => $designConfig['effects_config'] ?? [],
            'animation_config' => $designConfig['animation_config'] ?? [],
            'is_visible' => true,
            'is_active' => true,
        ]);
    }

    /**
     * Update child customizer design
     */
    public static function updateChildDesign(
        int $childCustomizerId,
        array $designConfig
    ): ChildCustomizer {
        $child = ChildCustomizer::findOrFail($childCustomizerId);

        $child->update([
            'shape_config' => $designConfig['shape_config'] ?? $child->shape_config,
            'color_config' => $designConfig['color_config'] ?? $child->color_config,
            'border_config' => $designConfig['border_config'] ?? $child->border_config,
            'shadow_config' => $designConfig['shadow_config'] ?? $child->shadow_config,
            'typography_config' => $designConfig['typography_config'] ?? $child->typography_config,
            'layout_config' => $designConfig['layout_config'] ?? $child->layout_config,
            'effects_config' => $designConfig['effects_config'] ?? $child->effects_config,
            'animation_config' => $designConfig['animation_config'] ?? $child->animation_config,
        ]);

        return $child;
    }

    /**
     * Apply color preset to child
     */
    public static function applyColorPresetToChild(int $childCustomizerId, int $presetId): ChildCustomizer
    {
        $preset = ColorPreset::findOrFail($presetId);
        $child = ChildCustomizer::findOrFail($childCustomizerId);

        $colorConfig = $child->color_config ?? [];
        $colorConfig['primary'] = $preset->primary_color;
        $colorConfig['secondary'] = $preset->secondary_color;
        $colorConfig['accent'] = $preset->accent_color;
        $colorConfig['text'] = $preset->text_color;

        $child->update(['color_config' => $colorConfig]);
        return $child;
    }

    /**
     * Generate CSS for child customizer
     */
    public static function generateChildCSS(int $childCustomizerId): string
    {
        $child = ChildCustomizer::findOrFail($childCustomizerId);
        return $child->generateCSS();
    }

    /**
     * Batch update children order
     */
    public static function batchUpdateChildrenOrder(array $childrenData): void
    {
        foreach ($childrenData as $data) {
            ChildCustomizer::find($data['id'])?->update(['display_order' => $data['order']]);
        }
    }

    /**
     * Clone child customizer
     */
    public static function cloneChildCustomizer(int $childCustomizerId, ?string $newName = null): ChildCustomizer
    {
        $original = ChildCustomizer::findOrFail($childCustomizerId);
        $clone = $original->replicate();

        if ($newName) {
            $clone->name = $newName;
        } else {
            $clone->name = $original->name . ' (Copy)';
        }

        $clone->save();
        return $clone;
    }

    /**
     * Export child customizer design
     */
    public static function exportChildDesign(int $childCustomizerId): array
    {
        $child = ChildCustomizer::findOrFail($childCustomizerId);

        return [
            'name' => $child->name,
            'description' => $child->description,
            'design_config' => [
                'shape_config' => $child->shape_config,
                'color_config' => $child->color_config,
                'border_config' => $child->border_config,
                'shadow_config' => $child->shadow_config,
                'typography_config' => $child->typography_config,
                'layout_config' => $child->layout_config,
                'effects_config' => $child->effects_config,
                'animation_config' => $child->animation_config,
            ],
        ];
    }

    /**
     * Import child design from export
     */
    public static function importChildDesign(int $themeChildId, array $designData, ?int $configOverrideId = null): ChildCustomizer
    {
        return self::createChildCustomizer(
            $themeChildId,
            $designData['design_config'],
            $configOverrideId
        );
    }

    /**
     * Save drawing data for child customizer
     */
    public static function saveDrawing(int $childCustomizerId, string $drawingData, array $metadata = []): ChildCustomizer
    {
        $child = ChildCustomizer::findOrFail($childCustomizerId);
        
        $child->update([
            'drawing_data' => $drawingData,
            'drawing_metadata' => $metadata,
        ]);
        
        return $child;
    }

    /**
     * Get drawing data for child customizer
     */
    public static function getDrawing(int $childCustomizerId): ?array
    {
        $child = ChildCustomizer::findOrFail($childCustomizerId);
        
        return [
            'drawing_data' => $child->drawing_data,
            'drawing_metadata' => $child->drawing_metadata,
            'child_info' => [
                'id' => $child->id,
                'name' => $child->name,
                'config_widget_override_id' => $child->config_widget_override_id,
            ]
        ];
    }

    /**
     * Get all drawings for a configuration
     */
    public static function getConfigurationDrawings(int $configWidgetOverrideId): array
    {
        return ChildCustomizer::where('config_widget_override_id', $configWidgetOverrideId)
            ->where('drawing_data', '!=', null)
            ->get()
            ->map(fn($child) => [
                'id' => $child->id,
                'name' => $child->name,
                'drawing_data' => $child->drawing_data,
                'drawing_metadata' => $child->drawing_metadata,
            ])
            ->toArray();
    }

    /**
     * Export child with drawing
     */
    public static function exportChildWithDrawing(int $childCustomizerId): array
    {
        $child = ChildCustomizer::findOrFail($childCustomizerId);
        
        return [
            'child_info' => [
                'id' => $child->id,
                'name' => $child->name,
                'description' => $child->description,
                'shape_config' => $child->shape_config,
                'color_config' => $child->color_config,
                'border_config' => $child->border_config,
                'shadow_config' => $child->shadow_config,
            ],
            'drawing' => [
                'drawing_data' => $child->drawing_data,
                'drawing_metadata' => $child->drawing_metadata,
                'exported_at' => now()->toIso8601String(),
            ]
        ];
    }
}

