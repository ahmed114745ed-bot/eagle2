<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\CP\Entities\CpRelation;

class Cp extends Model
{
    protected $guarded = ['id'];

    public function fromUser()
    {
        return $this->belongsTo(User::class,"user_one_id");
    }

    public function toUser()
    {
        return $this->belongsTo(User::class,"user_two_id");
    }

    public function relation()
    {
        return $this->belongsTo(CpRelation::class,"cp_relation_id");
    }

    public function scopeRelation($query)
    {
        return $query->whereHas('relation', function ($query) {
            $query->where('title', 'lover');
        });
    }
}
