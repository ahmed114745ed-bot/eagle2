<?php

namespace Modules\Reals\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class RealUserView extends Model
{
    protected $fillable = [];
    protected $table = 'real_user_views';
    protected $guarded = [];

    public function user()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }
}
