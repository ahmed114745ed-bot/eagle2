<?php

namespace App\Models;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class UserTarget extends Model
{
    use TimestampsWithTimezone;

    protected $table = 'user_target';

    protected $guarded = [];

    protected $casts = [
        'extras' => 'json',
    ];

    public function scopeOfAgency($q)
    {
        $user = Auth::user();
        if (Auth::user()->isRole('agency')) {
            $q->whereNotNull('agency_id')->where('agency_id', '=', @$user->agency_id);
        }
    }

    public function agency()
    {
        $agencyClass = config('agency-package.models.agency', \App\Models\Agency::class);
        if (!class_exists($agencyClass)) {
            return $this->belongsTo(self::class, 'agency_id')->whereRaw('1 = 0');
        }
        return $this->belongsTo($agencyClass, 'agency_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
