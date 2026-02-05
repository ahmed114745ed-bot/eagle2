<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Safe Class Alias for backward compatibility
 * Uses Null Object Pattern when Gifts package is not installed
 * 
 * @deprecated Use Utd\Gifts\Entities\Gift instead
 */
if (class_exists('\Utd\Gifts\Entities\Gift')) {
    class_alias(\Utd\Gifts\Entities\Gift::class, 'App\Models\Gift');
} else {
    /**
     * Null implementation when Gifts package is not installed
     */
    class Gift extends Model
    {
        protected $table = 'gifts';
        protected $guarded = [];
        
        // Prevent any operations
        public function save(array $options = [])
        {
            return false;
        }
    }
}
