<?php

namespace Modules\AgencyApp\Entities;

use App\Models\User;
use App\Models\Agency;
use App\Models\Country;
use Carbon\Carbon;
use Encore\Admin\Form\Field\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class AdditionalInfo extends Model
{
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
    public function agency()
    {
        return $this->belongsTo(Agency::class,);
    }

    public function country()
    {
        return $this->belongsTo(Country::class,);
    }

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
