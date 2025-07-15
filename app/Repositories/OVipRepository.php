<?php

namespace App\Repositories;

use Modules\Vip\Entities\OVip;
use Illuminate\Database\Eloquent\Model;

class OVipRepository
{
    public function getAllWithPrivileges()
    {
        return OVip::with('privilegs')->orderBy('level')->get();
    }

    public function findVipById($vipId)
    {
        return OVip::find($vipId);
    }
}