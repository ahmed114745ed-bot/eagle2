<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @deprecated Use Utd\Gifts\Entities\GiftRanking instead
 */
if (class_exists('\Utd\Gifts\Entities\GiftRanking')) {
    class_alias(\Utd\Gifts\Entities\GiftRanking::class, 'App\Models\GiftRanking');
} else {
    
  
    class GiftRanking extends Model
    {
        protected $table = 'gift_rankings';

        protected $fillable = [
            'type',
            'role',
            'ranker_id',
            'ranker_type',
            'total_gifts',
            'last_calculated_at',
        ];

        public function ranker(): MorphTo
        {
            return $this->morphTo();
        }
    }
}
