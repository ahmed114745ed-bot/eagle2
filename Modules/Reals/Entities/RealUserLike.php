<?php

namespace Modules\Reals\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class RealUserLike extends Model
{
    protected $guarded = [];
    protected $fillable = [];

    protected $table = 'real_user_likes';

    public function user()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }
}
