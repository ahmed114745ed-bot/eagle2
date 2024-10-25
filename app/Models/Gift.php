<?php

namespace App\Models;

use Modules\Moment\Entities\Moment;
use Illuminate\Database\Eloquent\Model;
use Modules\Achievement\Http\Traits\AchievementGift;

class Gift extends Model
{
    use AchievementGift;
   // protected $fillable=['use_count'];
   protected $guarded = ['id'];
    public function luckyGift()
    {
        return $this->hasOne(LuckyGift::class);
    }

    public function moments()
    {
        return $this->belongsToMany(Moment::class, 'moment_user_gifts')->withPivot('num', 'created_at','updated_at')->withTimestamps();
    }

    public function lucky_gift ()
    {
        return $this->hasOne(LuckyGift::class,'gift_id');
    }
}
