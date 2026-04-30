<?php

use Utd\Achievements\Entities\MonthlyDiamondReceive;

if (!function_exists('uploadMonthlyDiamondReceive')) {
    function uploadMonthlyDiamondReceive($user_id, $monthlyDiamondValue)
    {
        $date = \Carbon\Carbon::now(getTimezone());
        MonthlyDiamondReceive::updateOrCreate(
            [
                'user_id' => $user_id,
                'month' => $date->month,
                'year' => $date->year,
            ],
            [
                'monthly_diamond_received' => $monthlyDiamondValue,
            ]
        );
    }
}

if (!function_exists('incrementMonthlyDiamond')) {
    function incrementMonthlyDiamond($user_id, $value)
    {
        $date = \Carbon\Carbon::now(getTimezone());

        $monthlyRecord = MonthlyDiamondReceive::where('user_id', $user_id)
            ->where('month', $date->month)
            ->where('year', $date->year)
            ->lockForUpdate()
            ->first();

        if ($monthlyRecord) {
            $monthlyRecord->increment('monthly_diamond_received', $value);
        } else {
            MonthlyDiamondReceive::create([
                'user_id' => $user_id,
                'month'   => $date->month,
                'year'    => $date->year,
                'monthly_diamond_received' => $value,
            ]);
        }
    }
}
