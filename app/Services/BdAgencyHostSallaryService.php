<?php
namespace App\Services;

use App\Models\BdAgencyHostSallary;
use App\Models\BdSalary;

class BdAgencyHostSallaryService
{
    public static function storeOrUpdate(array $data): void
    {
        self::storeSallaryLine($data);
        $total = self::calculateTotalSallary($data['bd_id'], $data['month'], $data['year']);
        self::storeOrUpdateBdSalary($data['bd_id'], $data['month'], $data['year'], $total);
    }

    protected static function storeSallaryLine(array $data): void
    {
        $attributes = [
            'bd_id'     => $data['bd_id'],
            'user_id'   => $data['user_id'],
            'agency_id' => $data['agency_id'],
            'month'     => $data['month'],
            'year'      => $data['year'],
        ];

        $values = [
            'amount'           => $data['amount'],
            'user_sallary'     => $data['sallary'],
            'agency_sallary'   => $data['agency_sallary'],
        ];

        BdAgencyHostSallary::updateOrCreate($attributes, $values);
    }

    protected static function calculateTotalSallary(int $bdId, int $month, int $year): float
    {
        return BdAgencyHostSallary::where([
            'bd_id' => $bdId,
            'month' => $month,
            'year'  => $year,
        ])->sum('amount');
    }

    protected static function storeOrUpdateBdSalary(int $bdId, int $month, int $year, float $salary): void
    {
        $bdSalary = BdSalary::query()
            ->where([
                'bd_id' => $bdId,
                'month' => $month,
                'year'  => $year,
            ])
            ->lock()
            ->first();

        if ($bdSalary) {
            $bdSalary->update([
                'salary' => $salary,
            ]);
        } else {
            BdSalary::create([
                'bd_id'  => $bdId,
                'salary' => $salary,
                'month'  => $month,
                'year'   => $year,
            ]);
        }
    }
}
