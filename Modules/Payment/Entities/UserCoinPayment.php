<?php

namespace Modules\Payment\Entities;

use App\Models\Coin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserCoinPayment extends Model
{
    use HasFactory;

    protected $table = 'user_coin_payments';
    protected $guarded = [];
    protected $fillable = [];

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
    protected static function newFactory()
    {
//        return \Modules\Payment\Database\factories\UserCoinPaymentFactory::new();
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }

    public function coin()
    {
        return $this->hasOne(Coin::class, 'id','coin_id');
    }
}
