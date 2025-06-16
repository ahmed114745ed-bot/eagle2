<?php

namespace App\Models;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;

class UserBoxGift extends Model
{
    use TimestampsWithTimezone;

    protected $table = 'user_box_gifts';

    protected $guarded = ['id'];
}
