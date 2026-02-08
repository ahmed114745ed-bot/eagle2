<?php

namespace Utd\Gifts\Entities;

use Utd\Gifts\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class GiftCategory extends Model
{
    use HasFactory, TimestampsWithTimezone;
    
    protected $table = 'gift_categories';
    
    protected $guarded = [];
    
    protected $casts = [
        'title' => 'array',
    ];
    
    public $timestamps = true;
    
    protected static $unguarded = false;

    public $sortable = [
        'order_column_name' => 'sort',
        'sort_when_creating' => true,
    ];

    protected static function booted()
    {
        parent::booted();
        
        // Use package observer if exists, otherwise check for app observer
        $packageObserver = 'Utd\\Gifts\\Observers\\GiftCategoryObserver';
        $appObserver = 'App\\Observers\\GiftCategoryObserver';
        
        if (class_exists($packageObserver)) {
            static::observe($packageObserver);
        } elseif (class_exists($appObserver)) {
            static::observe($appObserver);
        }
    }

    /**
     * Get title as string for admin selects
     * Fixes: htmlspecialchars error when title is array
     */
    public function getTitleStringAttribute(): string
    {
        if (is_array($this->title)) {
            return $this->title[app()->getLocale()] ?? $this->title['ar'] ?? $this->title['en'] ?? '';
        }
        return (string) $this->title;
    }

    public function gifts()
    {
        return $this->hasMany(Gift::class, 'gift_category_id');
    }
}
