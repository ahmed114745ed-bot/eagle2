<?php

namespace Modules\FixedTarget\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class SpecialUser extends Model
{
    protected $fillable = [];
    public function user(){
        return $this->belongsTo(User::class);
    }
}
