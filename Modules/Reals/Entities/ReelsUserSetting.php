<?php

namespace Modules\Reals\Entities;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;

class ReelsUserSetting extends Model
{
    use TimestampsWithTimezone;

    protected $fillable = [];

    protected $guarded = ['id'];

    protected $table = 'reels_user_settings';
}
