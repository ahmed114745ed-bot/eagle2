<?php

namespace Modules\CP\Entities;

use Illuminate\Database\Eloquent\Model;

class Cp extends Model
{
    protected $guarded = ['id'];

    public function cpRelation()
    {
        return $this->belongsTo(CpRelation::class, 'cp_relation_id');
    }
}
