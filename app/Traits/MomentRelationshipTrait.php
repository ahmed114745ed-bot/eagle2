<?php

namespace App\Traits;

use Modules\Moment\Entities\Moment;
use Modules\Moment\Entities\MomentCommint;
use Modules\Moment\Entities\MomentLikes;

trait MomentRelationshipTrait
{
    public function moment_likes(){
        if (!class_exists(MomentLikes::class)) {
            return $this->hasMany(self::class, 'id', 'id')->whereRaw('1 = 0');
        }
        return $this->hasMany(MomentLikes::class);
    }

    public function moment_comments(){
        if (!class_exists(MomentCommint::class)) {
            return $this->hasMany(self::class, 'id', 'id')->whereRaw('1 = 0');
        }
        return $this->hasMany(MomentCommint::class);
    }

    public function moments(){
        if (!class_exists(Moment::class)) {
            return $this->hasMany(self::class, 'id', 'id')->whereRaw('1 = 0');
        }
        return $this->hasMany(Moment::class);
    }
}
