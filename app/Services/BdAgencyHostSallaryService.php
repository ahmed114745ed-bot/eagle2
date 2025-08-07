<?php
namespace App\Services;

use App\Models\Bd;
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
        $bdUserId = self::getBdAppId($data['bd_id']);
    
        if (self::hasSameSalaryWithAnotherBd($data)) {
            return; 
        }
    
        $attributes = self::buildAttributes($data, $bdUserId);
        $values = self::buildValues($data);
    
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

    protected static function getBdAppId(int $bdId): int
    {
        return Bd::find($bdId)?->app_id ?? 0;
    }
    
    protected static function hasSameSalaryWithAnotherBd(array $data): bool
    {
        return BdAgencyHostSallary::where('user_id', $data['user_id'])
            ->where('agency_id', $data['agency_id'])
            ->where('month', $data['month'])
            ->where('year', $data['year'])
            ->where('bd_id', '!=', $data['bd_id'])
            ->where('user_sallary', $data['sallary'])
            ->where('agency_sallary', $data['agency_sallary'])
            ->exists();
    }

    protected static function buildAttributes(array $data, int $bdUserId): array
    {
        return [
            'bd_id'       => $data['bd_id'],
            'bd_user_id'  => $bdUserId,
            'user_id'     => $data['user_id'],
            'agency_id'   => $data['agency_id'],
            'month'       => $data['month'],
            'year'        => $data['year'],
        ];
    }

    protected static function buildValues(array $data): array
    {
        return [
            'amount'           => $data['amount'],
            'user_sallary'     => $data['sallary'],
            'agency_sallary'   => $data['agency_sallary'],
        ];
    }



}
