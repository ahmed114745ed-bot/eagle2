<?php

namespace Modules\CP\Entities;

use Illuminate\Database\Eloquent\Model;

class CpRelation extends Model
{
    protected $guarded = [];

    public function levels(){
        return $this->hasMany(CpLevel::class, 'cp_relation_id');
    }
}
