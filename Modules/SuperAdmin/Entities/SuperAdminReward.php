<?php

namespace Modules\SuperAdmin\Entities;

use App\Models\User;
use App\Models\Ware;
use App\Models\Admin;
use App\Helpers\Common;
use Utd\Vip\Entities\OVip;
use Illuminate\Http\UploadedFile;
use App\Support\PackageHelper;
use Utd\Badge\Entities\Badge;
use App\Models\SuperPackageReward;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Modules\SuperAdmin\Entities\SuperAdmin;
use Utd\AreaManager\Entities\AreaManager;
use Utd\AreaManager\Entities\SubAreaManager;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SuperAdminReward extends Model
{
    use HasFactory;
    protected $table = 'admin_rewards';
    protected $guarded = [];
    protected $appends = ['target1', 'target2', 'target3', 'target4', 'target5'];

    public function superAdmin()
    {
        return $this->belongsTo(SuperAdmin::class, 'super_admin_id');
    }

    public function areaManager()
    {
        return $this->belongsTo(AreaManager::class, 'super_admin_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'super_admin_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }


    public function areaManagerDedicate()
    {
        return $this->belongsTo(AreaManager::class, 'created_by');
    }

    public function subAreaManagerDedicate()
    {
        return $this->belongsTo(SubAreaManager::class, 'created_by');
    }


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
        return PackageHelper::checkRelation($this, 'badge', 'hasOne') ??
            $this->hasOne(Badge::class, 'id', 'target');
    }

    public function packageRewards()
    {
        return $this->hasMany(SuperAdminReward::class, 'package_id', 'id')
            ->with(['ware', 'vip', 'badge']);
    }



    public function getTarget1Attribute()
    {
        return $this->target;
    }

    public function getTarget2Attribute()
    {
        return $this->target;
    }

    public function getTarget3Attribute()
    {
        return $this->target;
    }

    public function getTarget4Attribute()
    {
        return $this->target;
    }

    public function getTarget5Attribute()
    {
        return $this->target;
    }

    protected static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            if ($model->type === 'ware') {
                $model->target = request('target1', $model->target);
            } elseif ($model->type === 'vip') {
                $model->target = request('target2', $model->target);
            } elseif ($model->type === 'coins') {
                $model->target = request('target3', $model->target);
            } elseif ($model->type === 'badge') {
                $model->target = request('target5', $model->target);
            } elseif ($model->type === 'achievement') {

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
            unset($model->target5);
        });

        self::updating(function ($model) {
            if ($model->type === 'ware') {
                $model->target = request('target1', $model->target);
            } elseif ($model->type === 'vip') {
                $model->target = request('target2', $model->target);
            } elseif ($model->type === 'coins') {
                $model->target = request('target3', $model->target);
            } elseif ($model->type === 'badge') {
                $model->target = request('target5', $model->target);
            } elseif ($model->type === 'achievement') {
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
            unset($model->target5);
        });
    }
}
