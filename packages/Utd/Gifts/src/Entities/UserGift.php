<?php

namespace Utd\Gifts\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Utd\Gifts\Support\ModelResolver;

/**
 * UserGift Model
 * الهدايا التي يمتلكها المستخدم مع دعم ديناميكي للـ Models الخارجية
 */
class UserGift extends Model
{
    use HasFactory;

    protected $table = 'user_gifts';

    protected $fillable = ['gift_id', 'user_id', 'quantity', 'expire'];

    /**
     * الهدية
     */
    public function gift()
    {
        return $this->belongsTo(Gift::class, 'gift_id');
    }

    /**
     * المستخدم
     */
    public function user()
    {
        $userModel = ModelResolver::getUserModel();

        if (! $userModel) {
            return ModelResolver::emptyRelation($this);
        }

        return $this->belongsTo($userModel, 'user_id');
    }
}
