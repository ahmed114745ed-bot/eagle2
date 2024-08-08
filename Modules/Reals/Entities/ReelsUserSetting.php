<?php

namespace Modules\Reals\Entities;

use App\Models\Interest;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ReelsUserSetting extends Model
{
    protected $fillable = [];

    protected $guarded = ['id'];

    protected $table = 'reels_user_settings';

}
