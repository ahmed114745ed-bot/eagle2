<?php

namespace Utd\Gifts\Entities;

use App\Models\User;
use App\Support\PackageHelper;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;
use Utd\Moments\Entities\Moment;
use Utd\Vip\Entities\OVip;

class Gift extends Model
{
    use TimestampsWithTimezone;

    public $sortable = [
        'order_column_name' => 'sort',
        'sort_when_creating' => true,
    ];

    protected $table = 'gifts';

    protected $guarded = [];

    public function luckyGift()
    {
        return $this->hasOne(LuckyGift::class);
    }

    public function moments()
    {
        return PackageHelper::checkRelation($this, 'moment', 'belongsToMany')
            ?? $this->belongsToMany(Moment::class, 'moment_user_gifts')
                ->withPivot('num', 'created_at', 'updated_at')
                ->withTimestamps();
    }

    public function lucky_gift()
    {
        return $this->hasOne(LuckyGift::class, 'gift_id');
    }

    public function vip()
    {
        return PackageHelper::checkRelation($this, 'vip', 'hasOne')
            ?? $this->hasOne(OVip::class, 'id', 'vip_level');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_gifts')
            ->withPivot('quantity', 'expire')
            ->withTimestamps();
    }

    public function category()
    {
        return $this->belongsTo(GiftCategory::class, 'gift_category_id');
    }

    /**
     * @param  bool  $cpEnableAllGifts
     * @return bool
     */
    public function canPassToCp($cpEnableAllGifts)
    {
        if ($cpEnableAllGifts) {
            return true;
        }

        return $this->category && $this->category->type === 'cp';
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  bool  $cpEnableAllGifts
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCpAllowed($query, $cpEnableAllGifts)
    {
        if ($cpEnableAllGifts) {
            return $query;
        }

        return $query->whereHas('category', function ($q) {
            $q->where('type', 'cp');
        });
    }
}
