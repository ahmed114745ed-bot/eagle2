<?php

namespace App\SuperAdmin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\InfoBox;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{

    public function index(Content $content)
    {
        $appID = Auth::user()->id;
        $agencyCount = Agency::where('bd_id', $appID)->count();

//        $salaryData = \App\Models\BdSalary::where('bd_id', $appID)
//            ->selectRaw('COALESCE(SUM(salary),0) AS total_sallary, COALESCE(SUM(cut_amount),0) AS total_cut')
//            ->first();
//        $finalSalary = truncateAndTrim($salaryData->total_sallary - $salaryData->total_cut, 2);
//
//        $finalWallet = '';
        return $content
            ->title(__('Home'))
            ->description('إحصائيات عامة')

            ->row(function (Row $row) use ($agencyCount) {
                $row->column(6, new InfoBox(__('Agencies Count'), 'users', 'aqua', 'superadmin/agencies', $agencyCount));
//                $row->column(6, new InfoBox(__('BD Wallet'), 'money', 'green', 'bd/salaries', $finalSalary . ' 💰'));
            });
    }



}
