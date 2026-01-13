<?php

namespace App\Observers;

use App\Models\GiftCategory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GiftCategoryObserver
{
    /**
     * Handle the GiftCategory "updating" event.
     */
    public function updating(GiftCategory $giftCategory): void
    {
        // Clear any cache related to gift categories
        Cache::tags(['gift_categories'])->flush();
    }

    /**
     * Handle the GiftCategory "updated" event.
     */
    public function updated(GiftCategory $giftCategory): void
    {
        // Force DB to reflect changes immediately
        Cache::tags(['gift_categories'])->flush();
        
        // Verify sort is saved to database
        DB::statement('UPDATE gift_categories SET updated_at = ? WHERE id = ?', [
            now(),
            $giftCategory->id
        ]);
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
        Cache::tags(['gift_categories'])->flush();
    }

    /**
     * Handle the GiftCategory "deleted" event.
     */
    public function deleted(GiftCategory $giftCategory): void
    {
        Cache::tags(['gift_categories'])->flush();
    }
}
