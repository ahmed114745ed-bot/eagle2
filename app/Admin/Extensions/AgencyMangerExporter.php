<?php

namespace App\Admin\Extensions;

use App\Facades\ManagerHelper;
use App\Models\AdminUser;
use App\Models\User;
use App\Models\Agency;
use App\Models\AgencySallary;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

use Maatwebsite\Excel\Concerns\WithHeadings;

use Maatwebsite\Excel\Concerns\FromCollection;


class AgencyMangerExporter  implements FromCollection,WithHeadings
{


    protected $month;
    protected $year;

    public function __construct($month = null, $year = null)
    {
        $this->month = $month;
        $this->year = $year;
    }

    public function collection()
    {
        $managers = AdminUser::where('app_id', '!=', 0)->get();
        $arr = [];

        foreach ($managers as $manager) {
            $user = $manager->user;
            $uuid = $user?->uuid;
            $name = $user?->name;
            $app = $manager->app?->name ?? '---';
            $agencies = $manager->managerAgenciesWithoutScope()->get();

            $totalSalary = ManagerHelper::getTotalAgenciesSalary($agencies, $manager->app_id);
            $agencyCount = $agencies->count();

            $arr[] = [
                'id' => $manager->id,
                'name' => $name,
                'uuid' => $uuid,
                // 'app' => $app,
                'agency_count' => $agencyCount,
                'total_salary' => round($totalSalary, 2),
                // 'month' => $this->month,
                // 'year' => $this->year,
            ];
        }

        return collect($arr);
    }

    public function headings(): array
    {
        return [
            __('Id', [], 'ar'),
            __('Name', [], 'ar'),
            __('UUID', [], 'ar'),
            // __('App', [], 'ar'),
            __('Number of Agencies', [], 'ar'),
            __('Total Due Salary', [], 'ar'),
            // __('Month', [], 'ar'),
            // __('Year', [], 'ar'),
        ];
    }
}
