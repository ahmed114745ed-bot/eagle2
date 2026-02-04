<?php

namespace Utd\Gifts\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * GiftRanking Model
 */
class GiftRanking extends Model
{
    use HasFactory;

    protected $table = 'gift_rankings';

    protected $fillable = [
        'type',
        'role',
        'ranker_id',
        'ranker_type',
        'total_gifts',
        'last_calculated_at',
    ];

    /**
     * Get the related model (User, Room, etc.)
     */
    public function ranker(): MorphTo
    {
        return $this->morphTo();
    }
}
