<?php

namespace Modules\Reals\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class RealUserComment extends Model
{
    protected $fillable = [];

    protected $guarded = ['id'];

    public function user()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }
}
