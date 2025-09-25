<?php

namespace App\SuperAdmin\Controllers;

use App\Models\Bd;
use App\Models\Room;
use App\Models\User;
use App\Models\Agency;
use App\Models\UserSallary;
use Encore\Admin\Layout\Row;
use App\Models\AgencySallary;
use Encore\Admin\Layout\Content;
use Encore\Admin\Widgets\InfoBox;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{

    public function index(Content $content)
    {
        $countryID = Auth::user()->country_id;
        $usersCount = User::where('country_id', $countryID)->count();
        $agencyCount = Agency::where('country_id', $countryID)->count();
        $bdCount = Bd::where('parent_id', auth()->id())->count();
        $onlineUser = User::where('country_id', $countryID)->where('online', 1)->count();
        $diAuth = Auth::user()->di;
        $rooms = Room::whereHas('owner.country', function ($q) use ($countryID) {
            $q->where('id',  $countryID);
        })->whereHas('roomVisitors')->count();
        $user_salaries   = UserSallary::query()->whereHas('user', function ($q) use ($countryID) {
            $q->where('agency_id', '!=', 0)->where('country_id', $countryID);
        })->sum(DB::raw('sallary - cut_amount'));
        $agency_salaries = AgencySallary::query()->whereHas('agency', function ($q) use ($countryID) {
            $q->where('country_id', $countryID);
        })->sum(DB::raw('sallary - cut_amount'));

        //        $salaryData = \App\Models\BdSalary::where('bd_id', $appID)
        //            ->selectRaw('COALESCE(SUM(salary),0) AS total_sallary, COALESCE(SUM(cut_amount),0) AS total_cut')
        //            ->first();
        //        $finalSalary = truncateAndTrim($salaryData->total_sallary - $salaryData->total_cut, 2);
        //
        //        $finalWallet = '';
        return $content
            ->title(__('Home'))
            ->description('إحصائيات عامة')

            ->row(function (Row $row) use ($agencyCount, $usersCount, $bdCount, $onlineUser, $diAuth, $rooms, $agency_salaries,$user_salaries) {
                $row->column(6, new InfoBox(__('Users Count'), 'users', 'aqua', 'superadmin/users', $usersCount));
                $row->column(6, new InfoBox(__('online Users Count'), 'users', 'aqua', 'superadmin/users', $onlineUser));
                 $row->column(6, new InfoBox(__('total users salary'), 'users', 'aqua', 'superadmin/users', $user_salaries));
                $row->column(6, new InfoBox(__('Agencies Count'), 'building', 'aqua', 'superadmin/agencies', $agencyCount));
                $row->column(6, new InfoBox(__('total agency salary'), 'building', 'aqua', 'superadmin/agencies',  $agency_salaries));
                $row->column(6, new InfoBox(__('online rooms Count'), 'users', 'aqua', 'superadmin/rooms',  $rooms));
                $row->column(6, new InfoBox(__('Bd Count'), 'briefcase', 'aqua', 'superadmin/usersBD', $bdCount));
                $row->column(6, new InfoBox(__('you Wallet'), 'money', 'green', '/', $diAuth . ' 💰'));
            });
    }
}
