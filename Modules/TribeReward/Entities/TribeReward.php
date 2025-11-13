<?php

namespace Modules\TribeReward\Entities;

use App\Helpers\Common;
use App\Models\Ware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Vip\Entities\OVip;

class TribeReward extends Model
{
    protected $fillable = ['tribe_top_id', 'type', 'target_type', 'target_id', 'quantity', 'expire_days'];

    public function ware()
    {
        return $this->hasOne(Ware::class, 'id', 'target');
    }

    public function vip()
    {
        return $this->hasOne(OVip::class, 'id', 'target');
    }

    protected static function boot(): void
    {
        parent::boot();
        self::creating(function ($model) {
            if ($model->target_type == 'ware') {
                $model->target = request('target1', $model->target);
            } elseif ($model->target_type == 'vip') {
                $model->target = request('target2', $model->target);
            } elseif ($model->target_type == 'achievement') {
                $file = request('target4', $model->target);

                if ($file instanceof UploadedFile) {
                    $url = Common::upload('events', $file);
                }
                $model->target = $url ?? '';
            }
            unset($model->target1);
            unset($model->target2);
            unset($model->target3);
            unset($model->target4);
        });

        self::updating(function ($model) {
            if ($model->target_type == 'ware') {
                $model->target = request('target1', $model->target);
            } elseif ($model->target_type == 'vip') {
                $model->target = request('target2', $model->target);
            } elseif ($model->target_type == 'achievement') {
                $file = request('target4', $model->target);
                if ($file instanceof UploadedFile) {
                    $url = Common::upload('events', $file);
                    Storage::delete($model->target);
                }
                $model->target = $url ?? '';
            }
            unset($model->target1);
            unset($model->target2);
            unset($model->target3);
            unset($model->target4);
        });
    }

}
