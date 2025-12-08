<?php

namespace Modules\RankingReward\Entities;

use App\Models\Ware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Vip\Entities\OVip;

class RankingReward extends Model
{
    protected $fillable = ['ranking_range_id', 'target_type', 'target', 'expire_days'];

    public function rankingRange(): BelongsTo
    {
        return $this->belongsTo(RankingRange::class);
    }

    public function ware(): BelongsTo
    {
        return $this->belongsTo(Ware::class, 'target');
    }

    public function vip(): BelongsTo
    {
        return $this->belongsTo(OVip::class, 'target');
    }
}
