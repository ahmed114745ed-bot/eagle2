<?php

namespace Modules\CP\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CpInfo extends Model
{
    protected $fillable = [];

    protected $guarded = ['id'];

    public function userOne(){
        return $this->belongsTo(User::class , 'user_one_id');
    }

    public function userTwo(){
        return $this->belongsTo(User::class , 'user_two_id');
    }
}
