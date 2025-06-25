<?php

namespace App\Models;

use App\Traits\PreventDeleteIfCreatedByDeveloper;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;
use Modules\Achievement\Http\Traits\AchievementGift;
use Modules\Moment\Entities\Moment;

class Gift extends Model
{
    use AchievementGift, TimestampsWithTimezone,PreventDeleteIfCreatedByDeveloper;

    // protected $fillable=['use_count'];
    protected $guarded = ['id'];

    public function luckyGift()
    {
        return $this->hasOne(LuckyGift::class);
    }

    public function moments()
    {
        return $this->belongsToMany(Moment::class, 'moment_user_gifts')->withPivot('num', 'created_at', 'updated_at')->withTimestamps();
    }

    public function lucky_gift()
    {
        return $this->hasOne(LuckyGift::class, 'gift_id');
    }
    public function vip()
    {
        return $this->hasOne(OVip::class,'id','vip_level');
    }

    protected static function boot()
    {
        parent::boot();
        static::preventDeleteByDeveloper();

        // static::deleting(function ($gift) {

        //     if (auth()->user() && $gift->creator?->isRole('developer')) {
        //         abort(403);
        //     }
        // });
    }

    public function creator(){
        return $this->belongsTo(Admin::class, 'created_by');
    }
}
