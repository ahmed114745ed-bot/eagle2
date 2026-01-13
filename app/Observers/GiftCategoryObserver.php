<?php

namespace App\Observers;

use App\Models\GiftCategory;
use Illuminate\Support\Facades\Cache;

class GiftCategoryObserver
{
    /**
     * Handle the GiftCategory "updating" event.
     */
    public function updating(GiftCategory $giftCategory): void
    {
        // Clear any cache related to gift categories
        $this->clearCache();
    }

    /**
     * Handle the GiftCategory "updated" event.
     */
    public function updated(GiftCategory $giftCategory): void
    {
        // Clear cache after update
        $this->clearCache();
    }

    /**
     * Handle the GiftCategory "creating" event.
     */
    public function creating(GiftCategory $giftCategory): void
    {
        // Ensure sort has a default value
        if (is_null($giftCategory->sort)) {
            $giftCategory->sort = GiftCategory::max('sort') + 1;
        }
    }

    /**
     * Handle the GiftCategory "created" event.
     */
    public function created(GiftCategory $giftCategory): void
    {
        $this->clearCache();
    }

    /**
     * Handle the GiftCategory "deleted" event.
     */
    public function deleted(GiftCategory $giftCategory): void
    {
        $this->clearCache();
    }
    
    /**
     * Clear all related caches
     */
    protected function clearCache(): void
    {
        try {
            Cache::tags(['gift_categories', 'admin_data'])->flush();
            
            // Force opcache clear if available (for Octane)
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to clear cache in GiftCategoryObserver: ' . $e->getMessage());
        }
    }
}
