<?php

namespace Modules\CP\Entities;

use App\Models\Vip;
use Illuminate\Database\Eloquent\Model;

class CpLevelGift extends Model
{
    protected $guarded = ['id'];

    public function vip()
    {
        return $this->belongsTo(Vip::class);   
    }
}
