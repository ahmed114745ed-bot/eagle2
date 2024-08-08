<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pack extends Model
{
    protected $guarded = ['id'];

    public function ware()
    {
        return $this->belongsTo(Ware::class,'target_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function sender()
    {
        return $this->belongTo(User::class,'sender_id');
    }

}
