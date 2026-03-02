<?php

namespace App\Models;

use App\Support\PackageHelper;
use Utd\Badge\Entities\Badge;
use Utd\Vip\Entities\OVip;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SuperPackageReward extends Model
{
    use HasFactory;
    protected $guarded = [];


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
        return PackageHelper::checkRelation($this, 'vip', 'hasOne') ??
            $this->hasOne(OVip::class, 'id', 'target');
    }

    public function badge()
    {
        return $this->hasOne(Badge::class, 'id', 'target');
    }

}
