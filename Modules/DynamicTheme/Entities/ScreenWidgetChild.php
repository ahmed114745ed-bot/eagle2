<?php

namespace Modules\DynamicTheme\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScreenWidgetChild extends Model
{
    use HasFactory;

    protected $fillable = [
        'screen_widget_id',
        'child_key',
        'child_type',
        'label',
        'order',
        'is_visible',
        'is_active',
        'action',
        'assets',
        'position',
    ];

    protected $casts = [
        'order' => 'integer',
        'is_visible' => 'boolean',
        'is_active' => 'boolean',
        'action' => 'array',
        'assets' => 'array',
    ];

    /**
     * Get the screen widget that owns this child
     */
    public function screenWidget(): BelongsTo
    {
        return $this->belongsTo(ScreenWidget::class);
    }

    /**
     * Scope for visible children
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Scope for active children
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by child type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('child_type', $type);
    }

    /**
     * Scope for special children (search, join_room)
     */
    public function scopeSpecial($query)
    {
        return $query->where('child_type', 'special');
    }

    /**
     * Scope for regular children (tabs, categories)
     */
    public function scopeRegular($query)
    {
        return $query->where('child_type', '!=', 'special');
    }

    /**
     * Get child types
     */
    public static function getChildTypes(): array
    {
        return ['tab', 'category', 'special'];
    }

    /**
     * Check if this is a tab child
     */
    public function isTab(): bool
    {
        return $this->child_type === 'tab';
    }

    /**
     * Check if this is a category child
     */
    public function isCategory(): bool
    {
        return $this->child_type === 'category';
    }

    /**
     * Check if this is a special child
     */
    public function isSpecial(): bool
    {
        return $this->child_type === 'special';
    }
}
