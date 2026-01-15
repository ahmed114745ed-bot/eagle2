<?php

namespace Modules\DynamicTheme\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WidgetCustomizer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'config_widget_override_id',
        'shape_config',
        'color_config',
        'color_format',
        'gradient_config',
        'background_config',
        'border_config',
        'shadow_config',
        'animation_config',
        'transition_config',
        'typography_config',
        'layout_config',
        'effects_config',
        'name',
        'description',
        'is_active',
        'order',
    ];

    protected $casts = [
        'shape_config' => 'array',
        'color_config' => 'array',
        'gradient_config' => 'array',
        'background_config' => 'array',
        'border_config' => 'array',
        'shadow_config' => 'array',
        'animation_config' => 'array',
        'transition_config' => 'array',
        'typography_config' => 'array',
        'layout_config' => 'array',
        'effects_config' => 'array',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get the config widget override
     */
    public function configWidgetOverride(): BelongsTo
    {
        return $this->belongsTo(ConfigWidgetOverride::class, 'config_widget_override_id');
    }

    /**
     * Get generated CSS for this customizer
     */
    public function generateCSS(): string
    {
        $css = '';

        // Color CSS
        if ($this->color_config) {
            if (isset($this->color_config['primary'])) {
                $css .= "--color-primary: {$this->color_config['primary']};";
            }
            if (isset($this->color_config['secondary'])) {
                $css .= "--color-secondary: {$this->color_config['secondary']};";
            }
            if (isset($this->color_config['accent'])) {
                $css .= "--color-accent: {$this->color_config['accent']};";
            }
        }

        // Shape CSS
        if ($this->shape_config) {
            if (isset($this->shape_config['border_radius'])) {
                $css .= "border-radius: {$this->shape_config['border_radius']}px;";
            }
        }

        // Shadow CSS
        if ($this->shadow_config) {
            if (isset($this->shadow_config['box_shadow'])) {
                $css .= "box-shadow: {$this->shadow_config['box_shadow']};";
            }
        }

        // Typography CSS
        if ($this->typography_config) {
            if (isset($this->typography_config['font_size'])) {
                $css .= "font-size: {$this->typography_config['font_size']}px;";
            }
            if (isset($this->typography_config['font_weight'])) {
                $css .= "font-weight: {$this->typography_config['font_weight']};";
            }
            if (isset($this->typography_config['color'])) {
                $css .= "color: {$this->typography_config['color']};";
            }
        }

        // Layout CSS
        if ($this->layout_config) {
            if (isset($this->layout_config['padding'])) {
                $css .= "padding: {$this->layout_config['padding']}px;";
            }
            if (isset($this->layout_config['margin'])) {
                $css .= "margin: {$this->layout_config['margin']}px;";
            }
        }

        // Gradient CSS
        if ($this->gradient_config && isset($this->gradient_config['colors'])) {
            $colors = implode(', ', $this->gradient_config['colors']);
            $direction = $this->gradient_config['direction'] ?? 'to right';
            $css .= "background: linear-gradient({$direction}, {$colors});";
        }

        // Effects CSS
        if ($this->effects_config) {
            if (isset($this->effects_config['opacity'])) {
                $css .= "opacity: {$this->effects_config['opacity']};";
            }
        }

        // Animation CSS
        if ($this->animation_config) {
            if (isset($this->animation_config['name'])) {
                $duration = $this->animation_config['duration'] ?? '1s';
                $timing = $this->animation_config['timing_function'] ?? 'ease';
                $css .= "animation: {$this->animation_config['name']} {$duration} {$timing};";
            }
        }

        return $css;
    }

    /**
     * Generate CSS Classes from config
     */
    public function generateTailwindClasses(): array
    {
        $classes = [];

        // Colors
        if ($this->color_config) {
            $classes[] = 'custom-colors';
        }

        // Shape
        if ($this->shape_config && isset($this->shape_config['border_radius'])) {
            $radius = $this->shape_config['border_radius'];
            if ($radius == 0) $classes[] = 'rounded-none';
            elseif ($radius <= 4) $classes[] = 'rounded-sm';
            elseif ($radius <= 8) $classes[] = 'rounded';
            elseif ($radius <= 12) $classes[] = 'rounded-lg';
            elseif ($radius <= 16) $classes[] = 'rounded-xl';
            else $classes[] = 'rounded-2xl';
        }

        // Shadow
        if ($this->shadow_config && isset($this->shadow_config['box_shadow'])) {
            $classes[] = 'shadow-lg';
        }

        return $classes;
    }

    /**
     * Clone this customizer
     */
    public function clone(): self
    {
        return $this->replicate();
    }
};
