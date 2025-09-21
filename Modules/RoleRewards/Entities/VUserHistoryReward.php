<?php

namespace Modules\RoleRewards\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class VUserHistoryReward extends Model
{
    
    protected $fillable = [
        'user_id',
        'receive_type',
        'rewardable_id',
        'rewardable_type',
        'extra',
        'sub_type',
        'is_deleted'

    ];

    protected $casts = [
        'extra' => 'array',
    ];

    public function rewardable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public $timestamps = false;

    public function getRewardPreviewAttribute()
    {
        $reward = $this->rewardable;

        if (in_array($this->rewardable_type, [\App\Models\User::class, \Modules\Achievement\Entities\Achievement::class])) {
            $extra = $this->extra ?? '';
            $data = is_array($extra) ? $extra : json_decode($extra, true);

            if (!$data) {
                return $extra ?: 'N/A';
            }

            if ($this->rewardable_type === \App\Models\User::class) {
                return $data['reward'] ?? 0;
            }

            if ($this->rewardable_type === \Modules\Achievement\Entities\Achievement::class) {
                $path = $data['reward'] ?? 'achievement.png';
                $url = getImagePath($path);
                return handleShowImageWithTypes($this->id, $url, 50, 50);
            }
        }

        if (!$reward) {
            return 'N/A';
        }

        $name = $reward->name ?? 'Unnamed';
        $path = match ($this->rewardable_type) {
            \App\Models\Ware::class => $reward->img2 ?? $reward->show_img ?? '',
            \Modules\Vip\Entities\OVip::class => $reward->img ?? '',
            \Modules\Badge\Entities\Badge::class => $reward->image ?? '',
            default => 'coin.png',
        };

        $url = getImagePath($path);
        return handleShowImageWithTypes($this->id, $url, 50, 50) . $name;
    }






    
}
