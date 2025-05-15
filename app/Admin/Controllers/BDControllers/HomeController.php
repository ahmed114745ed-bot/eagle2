<?php

namespace App\Admin\Controllers\BDControllers;

use App\Admin\Customization\Dashboard\CustomDashboard;
use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\Gift;
use App\Models\Room;
use App\Models\User;
use App\Models\UserTarget;
use App\Models\Ware;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\InfoBox;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{

    public $permission_name = 'agent-home';


    public function index(Content $content)
    {
    
        $appID = Auth::user()->app_id;
        $agencyCount = Agency::where('bd_id', $appID)->count();
    
        // مجموع الرواتب
        $salaryData = \App\Models\BDSallary::where('bd_id', $appID)
            ->selectRaw('COALESCE(SUM(sallary),0) AS total_sallary, COALESCE(SUM(cut_amount),0) AS total_cut')
            ->first();
        $finalSalary = $salaryData->total_sallary - $salaryData->total_cut;
    
        // مجموع المحفظة
        $walletData = \App\Models\UserWallet::where('user_id', $appID)
            ->selectRaw('COALESCE(SUM(value),0) AS total_value, COALESCE(SUM(cut_amount),0) AS total_cut')
            ->first();
        $finalWallet = $walletData->total_value - $walletData->total_cut;
    
        return $content
            ->title('لوحة BD')
            ->description('إحصائيات عامة')
    
            ->row(function (Row $row) use ($agencyCount, $finalSalary, $finalWallet) {
                $row->column(4, new InfoBox('عدد الوكالات', 'users', 'aqua', '#', $agencyCount));
                $row->column(4, new InfoBox('إجمالي الرواتب', 'money', 'green', '#', number_format($finalSalary) . ' 💰'));
                $row->column(4, new InfoBox('رصيد المحفظة', 'credit-card', 'yellow', '#', number_format($finalWallet) . ' 💳'));
            });
    }
    
   

}
