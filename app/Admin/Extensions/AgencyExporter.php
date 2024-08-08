<?php

namespace App\Admin\Extensions;

use App\Models\User;
use App\Models\Agency;
use App\Models\AgencySallary;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

use Maatwebsite\Excel\Concerns\WithHeadings;

use Maatwebsite\Excel\Concerns\FromCollection;


class AgencyExporter  implements FromCollection,WithHeadings
{



    public $agency_id;
    protected $fileName = 'agencies_list.csv';
    protected $headings = [
        "id",
        "name",
        'salary',
        'expenses',
        'net salary',
        'agent',
        'month',
        'year'
    ];
    public $month;
    public $year;


    public function __construct($agency_id = null, $month = null, $year = null)
    {
        $this->agency_id = $agency_id;
        $this->month = $month;
        $this->year = $year;
    }

    /**
     * @inheritDoc
     */
    public function collection()
    {
        $agencies = Agency::query()->get();
        //$agencies = $agencies->get();
        $arr = [];

        foreach ($agencies as $agency) {
            $target = @$agency->target($this->month, $this->year);
            $agencySalarys =   AgencySallary::query()
            ->where('agency_id', '!=', null)
            ->where('is_paid', 0)->where('agency_id', $agency->id)->when(isset($target->month), function ($query) use ($target) {
                $query->where('month', '<=', $target->month);
            })->when(isset($target->year), function ($query) use ($target) {
                $query->where('year', '<=', $target->year);
            })
            ->orderBy('agency_id')->select(
                DB::raw('SUM(`sallary`) AS target'),
                DB::raw('SUM(`cut_amount`) AS expenses'),
                DB::raw('SUM(sallary) - SUM(cut_amount) AS salary'),
            )->groupBy('agency_id')->first();
           
            $item['id'] = $agency->id;
            $item['name'] = $agency->name;
            $item['salary'] = $agencySalarys->target ??"0";
            $item['expenses'] = $agencySalarys->expenses ??"0";
            $item['net_salary'] = $agencySalarys->target ??"0";
            $item['agent'] = @$agency->owner->name ?: @$agency->dashOwner->name;
            $item['month'] = @$target->month;
            $item['year'] = @$target->year;
            array_push($arr, $item);
    }
        return collect($arr);
    }


    public function headings(): array
    {
        return [
            __("id", [], 'ar'),
            __('name', [], 'ar'),
            __('salary', [], 'ar'),
            __('expenses', [], 'ar'),
            __('net_salary', [], 'ar'),
            __('agent', [], 'ar'),
            __('month', [], 'ar'),
            __('year', [], 'ar'),
        ];
    }
}
