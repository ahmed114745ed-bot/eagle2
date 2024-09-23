<?php

namespace App\Models;

use App\Selectables\Privileges;
use Illuminate\Database\Eloquent\Model;

class OVip extends Model
{
    protected $table = 'o_vips';
    protected $fillable = [
        'name', 
        'level', 
        'price', 
        'exp', 
        'expire', 
        
        'img' 
    ];

    protected $hidden = ['privileges'];

    public function privilegs(){
        return $this->belongsToMany (VipPrivilege::class,'vip_prev','o_vip_id','o_vip_privilege_id','id','id');
    }


 

    

    

}
