<?php

namespace App\Models;

use Carbon\Carbon;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RealtimeProject extends Model
{
    use HasFactory, TimestampsWithTimezone;

    protected $guarded = [];

    public function scopeFilterByMonthYearType($query,  $type)
    {
        return $query
            ->where('month', Carbon::now()->month)
            ->where('year', Carbon::now()->year)
            ->where('type', $type);
    }
}
