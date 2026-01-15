<?php

namespace Modules\DynamicTheme\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ChildCustomizer - تخصيص شكل الأطفال (Children)
 * 
 * يسمح بتخصيص شكل وألوان الأطفال داخل الويدجت
 */
class ChildCustomizer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'config_widget_override_id',
        'theme_child_id',
        'shape_config',
        'color_config',
        'gradient_config',
        'background_config',
        'border_config',
        'shadow_config',
        'animation_config',
        'transition_config',
        'typography_config',
        'layout_config',
        'effects_config',
        'position_config',
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
        'position_config' => 'array',
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
     * Get the theme child
     */
    public function themeChild(): BelongsTo
    {
        return $this->belongsTo(ThemeChild::class, 'theme_child_id');
    }

    /**
     * Generate CSS for this child customizer
     */
    public function generateCSS(): string
    {
        $css = '';

        // Shape CSS
        if ($this->shape_config) {
            if (isset($this->shape_config['border_radius'])) {
                $css .= "border-radius: {$this->shape_config['border_radius']}px;";
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
                $css .= "border: {$this->border_config['width']}px {$this->border_config['style'] ?? 'solid'} {$this->border_config['color']}};";
            }
        }

        // Shadow CSS
        if ($this->shadow_config) {
            if (isset($this->shadow_config['box_shadow'])) {
                $css .= "box-shadow: {$this->shadow_config['box_shadow']};";
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
     * Clone this customizer
     */
    public function clone(): self
    {
        return $this->replicate();
    }
}
