<?php

namespace App\Models;

use App\Helpers\Common;
use Modules\Vip\Entities\OVip;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SuperPackageReward extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $appends = [
        'wares',
        'badges',
        'vips',
        'coins',
        'achievement',
        'expire_ware',
        'quantity_ware',
        'expire_badge',
        'quantity_badge',
        'expire_vip',
        'quantity_vip',
        'expire_achievement',
    ];


    public function packageRewards()
    {
        return $this->hasMany(PackageReward::class, 'super_package_id');
    }

    public function ware()
    {
        return $this->hasOne(Ware::class, 'id', 'target');
    }

    public function vip()
    {
        return $this->hasOne(OVip::class, 'id', 'target');
    }

    public function badge()
    {
        return $this->hasOne(Badge::class, 'id', 'target');
    }

    public function getWaresAttribute()
    {
        return $this->packageRewards()
            ->where('type', 'ware')
            ->pluck('target')
            ->toArray();
    }

    // Badge
    public function getBadgesAttribute()
    {
        return $this->packageRewards()
            ->where('type', 'badge')
            ->pluck('target')
            ->toArray();
    }

    // VIP
    public function getVipsAttribute()
    {
        return $this->packageRewards()
            ->where('type', 'vip')
            ->pluck('target')
            ->toArray();
    }

    // Coins
    public function getCoinsAttribute()
    {
        return $this->packageRewards()
            ->where('type', 'coin')
            ->pluck('target')
            ->first();
    }

    // Achievement
    public function getAchievementAttribute()
    {
        return $this->packageRewards()
            ->where('type', 'achievement')
            ->pluck('target')
            ->first();
    }

    public function getExpireWareAttribute()
    {
        return $this->packageRewards()
            ->where('type', 'ware')
            ->pluck('expire')
            ->first();
    }
    public function getQuantityWareAttribute()
    {
        return $this->packageRewards()
            ->where('type', 'ware')
            ->pluck('quantity')
            ->first();
    }

    public function getExpireBadgeAttribute()
    {
        return $this->packageRewards()
            ->where('type', 'badge')
            ->pluck('expire')
            ->first();
    }
    public function getQuantityBadgeAttribute()
    {
        return $this->packageRewards()
            ->where('type', 'badge')
            ->pluck('quantity')
            ->first();
    }
    public function getExpireVipAttribute()
    {
        return $this->packageRewards()
            ->where('type', 'vip')
            ->pluck('expire')
            ->first();
    }
    public function getQuantityVipAttribute()
    {
        return $this->packageRewards()
            ->where('type', 'vip')
            ->pluck('quantity')
            ->first();
    }

    public function getExpireAchievementAttribute()
    {
        return $this->packageRewards()
            ->where('type', 'achievement')
            ->pluck('expire')
            ->first();
    }


    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {

            // Get all the extra data before unsetting
            $model->wares = array_filter(request('wares'));
            $model->badges = array_filter(request('badges'));
            $model->vips = array_filter(request('vips'));
            $model->coins = request('coins');
            $model->achievement = request('achievement');

            // Unset any attributes that don't exist in super_package_rewards table
            unset(
                $model->wares,
                $model->badges,
                $model->vips,
                $model->coins,
                $model->achievement,
                $model->expire_ware,
                $model->quantity_ware,
                $model->expire_badge,
                $model->quantity_badge,
                $model->quantity_vip,
                $model->expire_vip,
                $model->expire_achievement,

            );
        });

        static::saved(function ($model) {
            // Now save related PackageReward rows using the stored temp values
            $wares = array_filter(request('wares')) ?? [];
            $badges = array_filter(request('badges')) ?? [];
            $vips = array_filter(request('vips')) ?? [];
            $coins = request('coins') ?? null;
            $achievement = request('achievement') ?? null;
            $model->packageRewards()->whereIn('type', ['ware', 'badge', 'vip', 'coin', 'achievement'])->delete();
            // Wares
            foreach ($wares as $wareId) {
                $model->packageRewards()->create([
                    'type' => 'ware',
                    'target' => $wareId,
                    'expire' => request('expire_ware', 0),
                    'quantity' => request('quantity_ware', 0),
                ]);
            }

            // Badges
            foreach ($badges as $badgeId) {
                $model->packageRewards()->create([
                    'type' => 'badge',
                    'target' => $badgeId,
                    'expire' => request('expire_badge', 0),
                    'quantity' => request('quantity_badge', 0),
                ]);
            }

            // VIPs
            foreach ($vips as $vipId) {
                $model->packageRewards()->create([
                    'type' => 'vip',
                    'target' => $vipId,
                    'expire' => request('expire_vip', 0),
                    'quantity' => request('quantity_vip', 0),
                ]);
            }

            // Coins
            if (!empty($coins)) {
                $model->packageRewards()->create([
                    'type' => 'coin',
                    'target' => $coins,
                    'expire' => 0,
                    'quantity' => 0,
                ]);
            }

            // Achievement
            if ($achievement instanceof \Illuminate\Http\UploadedFile) {
                $url = Common::upload('package_reward', $achievement);
                $model->packageRewards()->create([
                    'type' => 'achievement',
                    'target' => $url,
                    'expire' => request('expire_achievement', 0),
                    'quantity' => 0,
                ]);
            }

            Cache::forget("super_package_rewards_{$model->id}");
        });


        static::deleted(function ($model) {
            Cache::forget("super_package_rewards_{$model->id}");
        });
    }
}
