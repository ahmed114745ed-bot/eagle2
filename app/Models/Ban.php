<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ban extends Model
{
    protected $guarded = ['id'];


    public function user()
    {
        return $this->hasOne(User::class, 'uuid', 'uid');
    }

    public function banType()
    {
        return $this->belongsTo(BanType::class,);
    }
}
