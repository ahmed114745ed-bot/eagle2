<?php

namespace Utd\Gifts\Entities;

use App\Observers\GiftCategoryObserver as AppGiftCategoryObserver;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Utd\Gifts\Observers\GiftCategoryObserver as PackageGiftCategoryObserver;

class GiftCategory extends Model
{
    use HasFactory, TimestampsWithTimezone;

    public $timestamps = true;

    public $sortable = [
        'order_column_name' => 'sort',
        'sort_when_creating' => true,
    ];

    protected $table = 'gift_categories';

    protected $guarded = [];

    protected $casts = [
        'title' => 'array',
    ];

    protected static $unguarded = false;

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

    protected static function booted()
    {
        parent::booted();

        // Use package observer if exists, otherwise check for app observer
        if (class_exists(PackageGiftCategoryObserver::class)) {
            static::observe(PackageGiftCategoryObserver::class);
        } elseif (class_exists(AppGiftCategoryObserver::class)) {
            static::observe(AppGiftCategoryObserver::class);
        }
    }
}
