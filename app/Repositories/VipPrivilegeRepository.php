<?php

namespace App\Repositories;

use App\Models\VipPrivilege;
use Illuminate\Database\Eloquent\Model;

class VipPrivilegeRepository
{
    public function getAllPrivileges()
    {
        return VipPrivilege::all();
    }
}