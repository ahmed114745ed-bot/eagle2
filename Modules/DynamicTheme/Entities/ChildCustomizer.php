<?php

namespace Modules\DynamicTheme\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChildCustomizer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'config_widget_override_id',
        'theme_child_id',
        'shape_config',
        'color_config',
        'border_config',
        'shadow_config',
        'typography_config',
        'layout_config',
        'effects_config',
        'animation_config',
        'position_config',
        'drawing_data',
        'drawing_metadata',
        'is_visible',
        'display_order',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'shape_config' => 'array',
        'color_config' => 'array',
        'border_config' => 'array',
        'shadow_config' => 'array',
        'typography_config' => 'array',
        'layout_config' => 'array',
        'effects_config' => 'array',
        'animation_config' => 'array',
        'position_config' => 'array',
        'drawing_metadata' => 'array',
        'is_visible' => 'boolean',
        'display_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the config widget override
     */
    public function configWidgetOverride(): BelongsTo
    {
        return $this->belongsTo(ConfigWidgetOverride::class, 'config_widget_override_id');
    }

    /**
     * Get the theme child
     */
    public function themeChild(): BelongsTo
    {
        return $this->belongsTo(ThemeChild::class, 'theme_child_id');
    }

    /**
     * Generate CSS for child
     */
    public function generateCSS(): string
    {
        $css = '';

        // Shape CSS
        if ($this->shape_config) {
            if (isset($this->shape_config['borderRadius'])) {
                $css .= "border-radius: {$this->shape_config['borderRadius']}px;";
            }
        }

        // Color CSS
        if ($this->color_config) {
            if (isset($this->color_config['background'])) {
                $css .= "background-color: {$this->color_config['background']};";
            }
            if (isset($this->color_config['text'])) {
                $css .= "color: {$this->color_config['text']};";
            }
        }

        // Border CSS
        if ($this->border_config) {
            if (isset($this->border_config['width'])) {
                $style = $this->border_config['style'] ?? 'solid';
                $css .= "border: {$this->border_config['width']}px {$style} {$this->border_config['color']};";
            }
        }

        // Shadow CSS
        if ($this->shadow_config) {
            if (isset($this->shadow_config['offsetX'])) {
                $offsetX = $this->shadow_config['offsetX'];
                $offsetY = $this->shadow_config['offsetY'] ?? 0;
                $blur = $this->shadow_config['blur'] ?? 0;
                $opacity = $this->shadow_config['opacity'] ?? 0.1;
                $css .= "box-shadow: {$offsetX}px {$offsetY}px {$blur}px rgba(0,0,0,{$opacity});";
            }
        }

        // Position CSS
        if ($this->position_config) {
            if (isset($this->position_config['top'])) {
                $css .= "top: {$this->position_config['top']}px;";
            }
            if (isset($this->position_config['left'])) {
                $css .= "left: {$this->position_config['left']}px;";
            }
            if (isset($this->position_config['width'])) {
                $css .= "width: {$this->position_config['width']}px;";
            }
            if (isset($this->position_config['height'])) {
                $css .= "height: {$this->position_config['height']}px;";
            }
        }

        // Layout CSS
        if ($this->layout_config) {
            if (isset($this->layout_config['padding'])) {
                $css .= "padding: {$this->layout_config['padding']}px;";
            }
        }

        // Effects CSS
        if ($this->effects_config) {
            if (isset($this->effects_config['opacity'])) {
                $css .= "opacity: {$this->effects_config['opacity']};";
            }
        }

        // Animation CSS
        if ($this->animation_config) {
            if (isset($this->animation_config['type'])) {
                $duration = $this->animation_config['duration'] ?? 300;
                $css .= "animation: {$this->animation_config['type']} {$duration}ms;";
            }
        }

        return $css;
    }

    /**
     * Get visibility status
     */
    public function getVisibilityStatus(): string
    {
        return $this->is_visible ? 'visible' : 'hidden';
    }

    /**
     * Clone this customizer
     */
    public function clone(): self
    {
        return $this->replicate();
    }
}
