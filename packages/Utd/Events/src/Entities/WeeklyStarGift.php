<?php

namespace Utd\Events\Entities;

use App\Support\PackageHelper;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Utd\Gifts\Entities\Gift;

class WeeklyStarGift extends Model
{
    use HasFactory, TimestampsWithTimezone;

    protected $fillable = ['id', 'gift_id', 'weekly_star_id'];

    public function gifts()
    {
        return PackageHelper::checkRelation($this, 'gift', 'hasMany')
            ?? $this->hasMany(Gift::class, 'gift_id');
    }
}
