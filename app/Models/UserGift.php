<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Safe Class Alias for backward compatibility
 * Uses Null Object Pattern when Gifts package is not installed
 * 
 * @deprecated Use Utd\Gifts\Entities\UserGift instead
 */
if (class_exists('\Utd\Gifts\Entities\UserGift')) {
    class_alias(\Utd\Gifts\Entities\UserGift::class, 'App\Models\UserGift');
} else {
    /**
     * Null implementation when Gifts package is not installed
     */
    class UserGift extends Model
    {
        protected $table = 'user_gifts';
        protected $guarded = [];
        
        public function save(array $options = [])
        {
            return false;
        }
    }
}
