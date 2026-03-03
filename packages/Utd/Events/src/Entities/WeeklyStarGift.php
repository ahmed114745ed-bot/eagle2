<?php

namespace Utd\Events\Entities;

use App\Models\Gift; // App\Models\Gift safely aliases Utd\Gifts\Entities\Gift when package is installed
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklyStarGift extends Model
{
    use HasFactory, TimestampsWithTimezone;

    protected $fillable = ['id', 'gift_id', 'weekly_star_id'];

    public function gifts()
    {
        return $this->hasMany(Gift::class, 'gift_id');
    }
}
