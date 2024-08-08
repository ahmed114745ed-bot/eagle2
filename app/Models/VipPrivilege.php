<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VipPrivilege extends Model
{
    protected $table = 'vip_privileges';

    public function getItem($vip){
        $i = Ware::query ()->where ('get_type',1)->where ('type',$this->type)->where('level',$vip)->first ();
        return $i;
    }

    public function vip(){
        return $this->belongsToMany (VipPrivilege::class,'vip_prev','o_vip_privilege_id', 'o_vip_id','id','id');
    }
}
