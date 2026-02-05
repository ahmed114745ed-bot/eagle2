<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Safe Class Alias for backward compatibility
 * Uses Null Object Pattern when Gifts package is not installed
 * 
 * @deprecated Use Utd\Gifts\Entities\GiftLog instead
 */
if (class_exists('\Utd\Gifts\Entities\GiftLog')) {
    class_alias(\Utd\Gifts\Entities\GiftLog::class, 'App\Models\GiftLog');
} else {
    /**
     * Null implementation when Gifts package is not installed
     */
    class GiftLog extends Model
    {
        protected $table = 'gift_logs';
        protected $guarded = [];
        
        // Prevent any operations
        public function save(array $options = [])
        {
            return false;
        }
    }
}
