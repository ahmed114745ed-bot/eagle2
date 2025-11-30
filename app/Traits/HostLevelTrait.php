<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;



trait HostLevelTrait
{


    public function scopeFilterByEventType(Builder $query, $eventType)
    {
        return $query
            ->when($eventType == 'daily', function ($query) {
                $query->whereDate('created_at', today());
            })
            ->when($eventType == 'weekly', function ($query) {
                $startOfWeek = Carbon::now()->startOfWeek(Carbon::SATURDAY);
                $endOfWeek   = Carbon::now()->endOfWeek(Carbon::FRIDAY);
                $query->whereBetween('created_at', [$startOfWeek, $endOfWeek]);
            })
            ->when($eventType == 'monthly', function ($query) {
                $startOfMonth = Carbon::now()->startOfMonth();
                $endOfMonth   = Carbon::now()->endOfMonth();
                $query->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
            });
    }
}
