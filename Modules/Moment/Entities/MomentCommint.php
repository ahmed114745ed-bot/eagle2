<?php

namespace Modules\Moment\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class MomentCommint extends Model
{
    protected $fillable = ['user_id','moment_id','comment'];
    protected $table = 'moment_user_comments';
    protected $guarded = ['id'];
    public function user()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }

}
