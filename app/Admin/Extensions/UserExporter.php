<?php

namespace App\Admin\Extensions;

use App\Models\User;
use App\Models\UserTarget;
use App\Models\UserSallary;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Encore\Admin\Grid\Exporters\ExcelExporter;
use Maatwebsite\Excel\Concerns\WithHeadings;

use Maatwebsite\Excel\Concerns\FromCollection;


class UserExporter implements FromCollection,WithHeadings
{



    protected $fileName = 'users_list.csv';
    protected $headings = [
        "id",
        "uuid",
        "name",
        'diamonds',
        'target',
        'expenses',
        'salary',
        'agency',
        'month',
        'year'
    ];





    /**
     * @inheritDoc
     */
    public function collection()
    {
        $users = User::query()
            ->where('agency_id', '!=', 0)
            ->where('agency_id', '!=', '')
            ->where('agency_id', '!=', null)
            //            ->where ('salary','>',0)
        ;
        if (request('agency_id')) {
            $users = $users->where('agency_id', request('agency_id'));
        }

        $users = $users->get();

        $arr = [];

        foreach ($users as $user) {
            $target = @$user->target(request('month'), request('year'));
            $userSalarys = UserSallary::where('user_id', $user->id)->when(isset($target->month), function ($query) use ($target) {
                $query->where('month', '<=', $target->month);
            })->when(isset($year), function ($query) use ($target) {
                $query->where('year', '<=', $target->year);
            })
        ->where('is_paid', 0)
        ->where('user_agency_id', '!=', null)->orderBy('user_id')
        ->select(
            DB::raw('SUM(sallary) AS target'),
            DB::raw('SUM(cut_amount) AS expenses'),
            DB::raw('SUM(sallary) - SUM(cut_amount) AS salary')
        )
        ->groupBy('user_id')
        ->first();

    $diamonds = UserTarget::where('user_id', $user->id)->when(isset($target->month), function ($query) use ($target) {
        $query->where('add_month', '<=', $target->month);
    })->when(isset($target->year), function ($query) use ($target) {
        $query->where('add_year', '<=', $target->year);
    })
        ->where('agency_id', '!=', null)
        ->select(
            DB::raw('SUM(user_diamonds) AS diamond')
        )->orderBy('user_id')
        ->groupBy('user_id')
        ->first();
      //  dd($diamonds->diamond);
           
                    
                    $item['id'] = $user->id;
                    $item['uuid'] = $user->uuid;
                    $item['name'] = $user->name;
                    $item['diamonds'] = $diamonds->diamond ??"0";
                    $item['target'] = $userSalarys->target ??"0";
                    $item['expenses'] = $userSalarys->expenses ??"0";
                    $item['salary'] = $userSalarys->salary?? "0";
                    $item['agency'] = @$user->agency->name;
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
            __("uuid", [], 'ar'),
            __('name', [], 'ar'),
            __('diamonds', [], 'ar'),
            __('target', [], 'ar'),
            __('expenses', [], 'ar'),
            __('salary', [], 'ar'),
            __('agency', [], 'ar'),
            __('month', [], 'ar'),
            __('year', [], 'ar'),
        ];
    }
}
