<?php

namespace Modules\RoomBoom\Entities;

use App\Helpers\Common;
use App\Models\Gift;
use App\Models\Ware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class RoomBoomReward extends Model
{
    protected $fillable = ['room_boom_level_id', 'target', 'target_type', 'priority', 'quantity', 'expire_days'];

     protected $guarded = ['ware_target_id', 'gift_target_id'];

    public function ware(): HasOne
    {
        return $this->hasOne(Ware::class, 'id', 'target');
    }

    public function gift(): HasOne
    {
        return $this->hasOne(Gift::class, 'id', 'target');
    }

    public function ware_target(): BelongsTo
    {
        return $this->belongsTo(Ware::class, 'target');
    }

    public function gift_target(): BelongsTo
    {
        return $this->belongsTo(Gift::class, 'target');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function ($model) {
            unset($model->ware_target_id, $model->gift_target_id);

            switch ($model->target_type) {
                case 'ware':
                    if (isset($model->ware_target_id)) {
                        $model->target = $model->ware_target_id;
                        unset($model->ware_target_id);
                    }
                    break;

                case 'gift':
                    if (isset($model->gift_target_id)) {
                        $model->target = $model->gift_target_id;
                        unset($model->gift_target_id);
                    }
                    break;

                case 'achievement':
                    if (request()->hasFile('achievement_target')) {
                        $file = request()->file('achievement_target');

                        if ($file instanceof UploadedFile) {
                            $url = Common::upload('roomBoom', $file);

                            $model->target = $url;
                        }
                    }
//                    if (isset($model->achievement_target)) {
//                        $model->target = $model->achievement_target;
//                        unset($model->achievement_target);
//                    }
                    unset($model->achievement_target);
                    break;

                case 'coin':
                    if (isset($model->coin_target)) {
                        $model->target = $model->coin_target;
                        unset($model->coin_target);
                    }
                    break;
            }
        });
        self::creating(function ($model) {
            if ($model->target_type == 'ware') {
                $model->target = request('target1', $model->target);
            } elseif ($model->target_type == 'achievement') {
                $file = request('target4', $model->target);

                if ($file instanceof UploadedFile) {
                    $url = Common::upload('events', $file);
                }
                $model->target = $url ?? '';
            } elseif ($model->target_type == 'gift') {
                $model->target = request('target5', $model->target);
            } elseif ($model->target_type == 'coin') {
                $model->target = request('target6', $model->target);
            }
            unset($model->target1);
            unset($model->target4);
            unset($model->target5);
            unset($model->target6);
        });

        self::updating(function ($model) {
            if ($model->target_type == 'ware') {
                $model->target = request('target1', $model->target);
            } elseif ($model->target_type == 'achievement') {
                $file = request('target4', $model->target);
                if ($file instanceof UploadedFile) {
                    $url = Common::upload('events', $file);
                    Storage::delete($model->target);
                }
                $model->target = $url ?? '';
            } elseif ($model->target_type == 'gift') {
                $model->target = request('target5', $model->target);
            } elseif ($model->target_type == 'coin') {
                $model->target = request('target6', $model->target);
            }
            unset($model->target1);
            unset($model->target4);
            unset($model->target5);
            unset($model->target6);
        });
    }
}
