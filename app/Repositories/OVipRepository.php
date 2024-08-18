<?php

namespace App\Repositories;

use App\Models\OVip;
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