<?php

namespace App\Models;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;

class AgencySallary extends Model
{
    use TimestampsWithTimezone;

    protected $table = 'agency_sallaries';

    protected $guarded = ['id'];

    public function getTotalSalaryAttribute()
    {
        return floor($this->sallary - $this->cut_amount);
    }

    protected static function booted()
    {
        self::saved(function ($model) {
            if ($model->agency_id) {
                clearAgencyCache($model->agency_id);
            }
        });

        self::deleted(function ($model) {
            if ($model->agency_id) {
                clearAgencyCache($model->agency_id);
            }
        });
    }
}
