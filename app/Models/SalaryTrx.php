<?php

namespace App\Models;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;

class SalaryTrx extends Model
{
    use TimestampsWithTimezone;

    protected $table = 'salary_trxs';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'oid');
    }

    public function agency()
    {
        $agencyClass = config('agency-package.models.agency', \App\Models\Agency::class);
        if (!class_exists($agencyClass)) {
            return $this->belongsTo(self::class, 'oid')->whereRaw('1 = 0');
        }
        return $this->belongsTo($agencyClass, 'oid');
    }
}
