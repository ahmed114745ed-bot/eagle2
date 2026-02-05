<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Safe Class Alias for backward compatibility
 * Uses Null Object Pattern when Gifts package is not installed
 * 
 * @deprecated Use Utd\Gifts\Entities\GiftCategory instead
 */
if (class_exists('\Utd\Gifts\Entities\GiftCategory')) {
    class_alias(\Utd\Gifts\Entities\GiftCategory::class, 'App\Models\GiftCategory');
} else {
    /**
     * Null implementation when Gifts package is not installed
     */
    class GiftCategory extends Model
    {
        protected $table = 'gift_categories';
        protected $guarded = [];
        
        public function save(array $options = [])
        {
            return false;
        }
    }
}
