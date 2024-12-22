<?php

namespace Modules\CP\Entities;

use App\Models\Vip;
use Illuminate\Database\Eloquent\Model;

class CpLevel extends Model
{
    protected $fillable = [];

/*     public function gifts(){
        return $this->hasManyThrough(
            CpLevelGift::class, // Final model (Gifts)
            Vip::class,         // Intermediate model (Vips)
            'id',               // Local key on Vips (relates to cp_level_gifts.vip_id)
            'vip_id',           // Foreign key on cp_level_gifts
            'id',               // Local key on cp_levels
            'id'                // Local key on Vips
        );
    } */

    public function gifts(){
        return $this->hasMany(CpLevelGift::class, 'vip_id', 'id');
    }
}
