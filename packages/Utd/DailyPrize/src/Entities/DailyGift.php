<?php

namespace Utd\DailyPrize\Entities;

use App\Helpers\Common;
use App\Models\Ware;
use App\Support\PackageHelper;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Badge\Entities\Badge;
use Utd\Vip\Entities\OVip;

class DailyGift extends Model
{
    use HasFactory, TimestampsWithTimezone;

    protected $guarded = [];

    protected $appends = ['target1', 'target2', 'target3', 'target4', 'target5'];

    public function ware()
    {
        return $this->hasOne(Ware::class, 'id', 'target');
    }

    public function vip()
    {
        return PackageHelper::checkRelation($this, 'vip', 'hasOne') ??
            $this->hasOne(OVip::class, 'id', 'target');
    }

    public function badge()
    {
        return $this->hasOne(Badge::class, 'id', 'target');
    }

    public function getTarget5Attribute()
    {
        return $this->target;
    }

    public function getTarget1Attribute()
    {
        return $this->gift_type === 'ware' ? $this->target : null;
    }

    public function getTarget2Attribute()
    {
        return $this->gift_type === 'vip' ? $this->target : null;
    }

    public function getTarget3Attribute()
    {
        return $this->gift_type === 'coins' ? $this->target : null;
    }

    public function getTarget4Attribute()
    {
        return $this->gift_type === 'achievement' ? $this->target : null;
    }

    protected static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            if ($model->gift_type === 'ware') {
                $model->target = request('target1', $model->target);
            } elseif ($model->gift_type === 'vip') {
                $model->target = request('target2', $model->target);
            } elseif ($model->gift_type === 'coins') {
                $model->target = request('target3', $model->target);
            } elseif ($model->gift_type === 'badge') {
                $model->target = request('target5', $model->target);
            } elseif ($model->gift_type === 'achievement') {
                $file = request('target4', $model->target);
                if ($file instanceof UploadedFile) {
                    $url = Common::upload(DIRECTORY_SEPARATOR.'events', $file);
                }
                $model->target = $url ?? '';
            }
            unset($model->target1);
            unset($model->target2);
            unset($model->target3);
            unset($model->target4);
            unset($model->target5);
        });

        self::updating(function ($model) {
            if ($model->gift_type === 'ware') {
                $model->target = request('target1', $model->target);
            } elseif ($model->gift_type === 'vip') {
                $model->target = request('target2', $model->target);
            } elseif ($model->gift_type === 'coins') {
                $model->target = request('target3', $model->target);
            } elseif ($model->gift_type === 'badge') {
                $model->target = request('target5', $model->target);
            } elseif ($model->gift_type === 'achievement') {
                $file = request('target4', $model->target);
                if ($file instanceof UploadedFile) {
                    $url = Common::upload(DIRECTORY_SEPARATOR.'events', $file);
                    $file = str_replace('\\', '/', $model->target);
                    Storage::delete($file);
                }
                $model->target = $url ?? '';
            }
            unset($model->target1);
            unset($model->target2);
            unset($model->target3);
            unset($model->target4);
            unset($model->target5);
        });

        self::deleted(function ($model) {
            \App\Facades\RedisService::update('daily-gift-count', DailyGift::count());
        });
        self::created(function ($model) {
            \App\Facades\RedisService::update('daily-gift-count', DailyGift::count());
        });
        self::updated(function ($model) {
            \App\Facades\RedisService::update('daily-gift-count', DailyGift::count());
        });
    }
}
