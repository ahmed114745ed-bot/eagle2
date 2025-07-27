<?php

namespace Modules\TribeReward\Entities;

use App\Helpers\Common;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class TribeReward extends Model
{
    protected $fillable = ['tribe_top_id', 'type', 'target_type', 'target_id', 'quantity', 'expire_days'];

    protected static function boot(): void
    {
        parent::boot();
        self::creating(function ($model) {
            if ($model->target_type == 'ware') {
                $model->target_id = request('target1', $model->target);
            } elseif ($model->target_type == 'vip') {
                $model->target_id = request('target2', $model->target);
            } elseif ($model->target_type == 'coins') {
                $model->target_id = request('target3', $model->target);
            } elseif ($model->target_type == 'achievement') {
                $file = request('target4', $model->target);

                if ($file instanceof UploadedFile) {
                    $url = Common::upload('events', $file);
                }
                $model->target_id = $url ?? '';
            }
            unset($model->target1);
            unset($model->target2);
            unset($model->target3);
            unset($model->target4);
        });

        self::updating(function ($model) {
            if ($model->target_type == 'ware') {
                $model->target_id = request('target1', $model->target);
            } elseif ($model->target_type == 'vip') {
                $model->target_id = request('target2', $model->target);
            } elseif ($model->target_type == 'coins') {
                $model->target_id = request('target3', $model->target);
            } elseif ($model->target_type == 'achievement') {
                $file = request('target4', $model->target);
                if ($file instanceof UploadedFile) {
                    $url = Common::upload('events', $file);
                    Storage::delete($model->target);
                }
                $model->target_id = $url ?? '';
            }
            unset($model->target1);
            unset($model->target2);
            unset($model->target3);
            unset($model->target4);
        });
    }

}
