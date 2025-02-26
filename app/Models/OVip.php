<?php

namespace App\Models;

use App\Selectables\Privileges;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class OVip extends Model
{
    protected $table = 'o_vips';
    protected $fillable = [
        'name',
        'level',
        'price',
        'exp',
        'expire',

        'img'
    ];

    protected $hidden = ['privileges'];

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
    public function privilegs()
    {
        return $this->belongsToMany(VipPrivilege::class, 'vip_prev', 'o_vip_id', 'o_vip_privilege_id', 'id', 'id');
    }

    public function wareIcon()
    {
        return $this->hasOne(Ware::class, 'level', 'level')
            ->where('type', 12)
            ->where('get_type', 1);
    }

    function getTranslation($key) {
        switch ($key) {
            case 1:
                return trans('Gemstone');
            case 3:
                return trans('Card Scroll');
            case 4:
                return trans('Avatar Frame');
            case 5:
                return trans('Bubble Frame');
            case 6:
                return trans('Entering Special Effects');
            case 7:
                return trans('Microphone Aperture');
            case 8:
                return trans('Badge');
            case 9:
                return trans('NoKick');
            case 10:
                return trans('Icon');
            case 11:
                return trans('intro animation');
            case 12:
                return trans('wapel');
            case 13:
                return trans('hide country and last login');
            case 14:
                return trans('vip gifts');
            case 15:
                return trans('no pan');
            case 16:
                return trans('hidden room');
            case 17:
                return trans('anonymous man');
            case 18:
                return trans('colored name');
            case 19:
                return trans('profile visitors hide in');
            case 20:
                return trans('hide last active');
            case 28:
                return trans('profile frame');
            default:
                return trans('Unknown'); // Fallback for unknown keys
        }
    }
}
