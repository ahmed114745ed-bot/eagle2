<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\CP\Entities\CpInfo;

class GiftLog extends Model
{
    protected $table = 'gift_logs';

    protected $guarded = ['id'];

    public function gift(){
        return $this->belongsTo (Gift::class,'giftId','id');
    }

    public function sender(){
        return $this->belongsTo (User::class,'sender_id');
    }

    public function receiver(){
        return $this->belongsTo (User::class,'receiver_id');
    }

    public function roomOwner(){
        return $this->belongsTo (User::class,'roomowner_id');
    }

    public function cp(){
        return $this->belongsTo(CpInfo::class,'cp_id')->with("userOne:id,uuid,name","userTwo:id,uuid,name");
    }

}
