<?php

namespace Utd\Achievements\Entities;

use App\Models\User;
use App\Support\PackageHelper;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Utd\Gifts\Entities\Gift;

class GiftAchievement extends Model
{
    use HasFactory, TimestampsWithTimezone;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function gift()
    {
        return PackageHelper::checkRelation($this, 'gift', 'belongsTo')
            ?? $this->belongsTo(Gift::class, 'gift_id');
    }

    public function Achievement()
    {
        return $this->belongsTo(Achievement::class, 'achievement_id');
    }
}
