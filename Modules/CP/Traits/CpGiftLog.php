<?php

namespace Modules\CP\Traits;

trait CpGiftLog
{
    public function cps()
    {
        return $this->belongsTo(Cp::class, 'cp_id');
    }

    public function cp()
    {
        return $this->belongsTo(Cp::class, 'cp_id')->with('fromUser:id,uuid,name', 'toUser:id,uuid,name', 'level');
    }
}
