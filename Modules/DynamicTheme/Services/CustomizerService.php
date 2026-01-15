<?php

namespace Modules\DynamicTheme\Services;

use Modules\DynamicTheme\Entities\WidgetCustomizer;
use Modules\DynamicTheme\Entities\ColorPreset;
use Modules\DynamicTheme\Entities\WidgetDesignTemplate;
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
}
