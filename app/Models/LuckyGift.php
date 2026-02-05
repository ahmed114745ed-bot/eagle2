<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Safe Class Alias for backward compatibility
 * Uses Null Object Pattern when Gifts package is not installed
 * 
 * @deprecated Use Utd\Gifts\Entities\LuckyGift instead
 */
if (class_exists('\Utd\Gifts\Entities\LuckyGift')) {
    class_alias(\Utd\Gifts\Entities\LuckyGift::class, 'App\Models\LuckyGift');
} else {
    /**
     * Null implementation when Gifts package is not installed
     */
    class LuckyGift extends Model
    {
        protected $table = 'lucky_gifts';
        protected $guarded = [];
        
        public function save(array $options = [])
        {
            return false;
        }
    }
}
