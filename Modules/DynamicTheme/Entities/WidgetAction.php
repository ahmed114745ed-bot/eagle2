<?php

namespace Modules\DynamicTheme\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WidgetAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'widget_id',
        'action_type',
        'action_label',
        'requires_target',
        'target_type',
        'description',
        'is_active',
    ];

    protected $casts = [
        'requires_target' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the widget that owns this action
     */
    public function widget(): BelongsTo
    {
        return $this->belongsTo(Widget::class);
    }

    /**
     * Scope for active actions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by action type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('action_type', $type);
    }

    /**
     * Get action types
     */
    public static function getActionTypes(): array
    {
        return ['screen', 'webview', 'filter', 'internal'];
    }

    /**
     * Get target types
     */
    public static function getTargetTypes(): array
    {
        return ['screen_key', 'url', 'filter_key', 'action_key'];
    }
}
