<?php

namespace Modules\SwitchAccount\Entities;

use App\Models\User;
use App\Models\Ware;
use Carbon\Carbon;
use Encore\Admin\Form\Field\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class UserAccount extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function getCreatedAtAttribute($value)
    {
        $timeZone = request()->header('tz') ?? 'UTC';
        //$timeZone = 'Asia/Dhaka'; // Get the user's time zone from the session
        return Carbon::parse($value)->setTimezone($timeZone)->format('Y-m-d H:i:s');
    }

    // Convert updated_at to the user's local time zone
    public function getUpdatedAtAttribute($value)
    {
        $timeZone = request()->header('tz') ?? 'UTC';
        //$timeZone = 'Asia/Dhaka'; // Get the user's time zone from the session
        return Carbon::parse($value)->setTimezone($timeZone)->format('Y-m-d H:i:s');
    }
    public function parent_user()
    {
        return $this->hasOne(User::class,'parent_user_id');
    }

    public function child_user()
    {
        return $this->hasOne(User::class,'child_user_id');
    }
}
