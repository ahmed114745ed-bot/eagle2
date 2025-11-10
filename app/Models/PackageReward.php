<?php

namespace App\Models;

use Modules\Vip\Entities\OVip;
use Modules\Badge\Entities\Badge;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PackageReward extends Model
{
    use HasFactory;
    protected $guarded = [];

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
}
