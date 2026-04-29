<?php

namespace Utd\Moments\Entities;

use App\Models\User;
use App\Support\PackageHelper;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;
use Utd\Gifts\Entities\Gift;

class MomentUserGift extends Model
{
    use TimestampsWithTimezone;

    protected $table = 'moment_user_gifts';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function moment()
    {
        return $this->belongsTo(Moment::class, 'moment_id');
    }

    public function gift()
    {
        return PackageHelper::checkRelation($this, 'gift', 'belongsTo') ??
            $this->belongsTo(Gift::class, 'gift_id');
    }
}
