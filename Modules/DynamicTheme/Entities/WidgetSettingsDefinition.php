<?php

namespace Modules\DynamicTheme\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WidgetSettingsDefinition extends Model
{
    use HasFactory;

    protected $fillable = [
        'widget_id',
        'setting_key',
        'setting_label',
        'setting_type',
        'setting_category',
        'default_value',
        'options',
        'validation_rules',
        'is_hidden',
        'order',
    ];

    protected $casts = [
        'options' => 'array',
        'validation_rules' => 'array',
        'is_hidden' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get the widget that owns this setting definition
     */
    public function widget(): BelongsTo
    {
        return $this->belongsTo(Widget::class);
    }

    /**
     * Scope for primary settings
     */
    public function scopePrimary($query)
    {
        return $query->where('setting_category', 'primary');
    }

    /**
     * Scope for secondary settings
     */
    public function scopeSecondary($query)
    {
        return $query->where('setting_category', 'secondary');
    }

    /**
     * Scope for visible settings (not hidden)
     */
    public function scopeVisible($query)
    {
        return $query->where('is_hidden', false);
    }

    /**
     * Scope for hidden settings
     */
    public function scopeHidden($query)
    {
        return $query->where('is_hidden', true);
    }

    /**
     * Get setting types
     */
    public static function getSettingTypes(): array
    {
        return ['text', 'number', 'boolean', 'select', 'color', 'json', 'range'];
    }

    /**
     * Get setting categories
     */
    public static function getSettingCategories(): array
    {
        return ['primary', 'secondary'];
    }

    /**
     * Cast default value based on setting type
     */
    public function getCastedDefaultValue()
    {
        if ($this->default_value === null) {
            return null;
        }

        return match ($this->setting_type) {
            'number', 'range' => (float) $this->default_value,
            'boolean' => filter_var($this->default_value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($this->default_value, true),
            default => $this->default_value,
        };
    }
}
