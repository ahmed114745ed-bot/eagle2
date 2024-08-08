<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEarnInvitation extends Model
{
    use HasFactory;
    protected $fillable=["id","parent_id","user_id","user_charge","parent_percentage"];

    public function user(){
        return $this->belongsTo(User::class)->with("profile:id,user_id,avatar as image");
    }
}
