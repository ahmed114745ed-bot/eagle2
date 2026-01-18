<?php

namespace Modules\Moment\Traits;

use Modules\Moment\Entities\Moment;

trait MomentRelationshipTrait
{
    public function moment_likes(){
        if (! class_exists(Moment::class)){
            return $this->hasMany(self::class, 'id', 'id')->whereRaw('1 = 0');
        }
        return $this->hasMany(\Modules\Moment\Entities\MomentLikes::class);
    }

    public function moment_comments(){
        if (! class_exists(Moment::class)){
            return $this->hasMany(self::class, 'id', 'id')->whereRaw('1 = 0');
        }
        return $this->hasMany(\Modules\Moment\Entities\MomentCommint::class);
    }

    public function moments(){
        if (! class_exists(Moment::class)){
            return $this->hasMany(self::class, 'id', 'id')->whereRaw('1 = 0');
        }
        return $this->hasMany(\Modules\Moment\Entities\Moment::class);
    }
}
