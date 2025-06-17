<?php

namespace Modules\Moment\Entities;

use App\Models\User;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;

class MomentUserGift extends Model
{
    use TimestampsWithTimezone;

    protected $table = 'moment_user_gifts';

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
