<?php

namespace App\Admin\Controllers;

use App\Models\User;
use App\Models\Charge;
use App\Helpers\Common;
use App\Models\CoinLog;
use App\Models\GameWallet;
use App\Models\UsdTransfer;
use App\Models\UserSallary;
use App\Models\AgencySallary;
use Encore\Admin\Layout\Content;
use App\Models\GameChargeHistory;
use App\Models\RequestTakeSalary;
use Encore\Admin\Widgets\InfoBox;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class AllStatisticController extends MainController
{
    public $permission_name = 'all-statistic';

    public function index(Content $content)
    {
        $userId = \Encore\Admin\Facades\Admin::user()->id;
        if ($userId == 1) {
            $coins                      = User::sum("di");
            $total_monthly_di_recieved  = User::sum("monthly_diamond_received");
            $user_sallaries   = UserSallary::query()->whereHas('user', function ($q) {
                $q->where('agency_id', '!=', 0);
            })->sum(DB::raw('sallary - cut_amount'));
            $agency_sallaries = AgencySallary::query()->sum(DB::raw('sallary - cut_amount'));
            $onlineUsers = DB::table('users')->selectRaw('device_token')->where('online_time', '>=', now()->startOfDay()->timestamp)->where('online_time', '<=', now()->timestamp)->groupBy(['device_token'])->get()->count() ?? 0;
            $allUsersCount = User::query()->count();
            $total_sallary    = $user_sallaries + $agency_sallaries;

            $lose1 = UsdTransfer::whereMonth("created_at", date("m"))->whereYear("created_at", date("Y"))->sum("value");
            $lose2 = RequestTakeSalary::where("status", 1)->whereMonth("created_at", date("m"))->whereYear("created_at", date("Y"))->sum("amount");
            $lose = $lose1 + $lose2;
            $first_earned_charge = Charge::where("charger_type", "dash")->whereMonth("created_at", date("m"))->whereYear("created_at", date("Y"))->sum("usd");
            $second_earned_charge = CoinLog::whereIn('method', ['huawei_pay', 'google_pay', 'apple_pay'])->whereMonth("created_at", date("m"))->whereYear("created_at", date("Y"))->sum("paid_usd");
            $earned_charge = $first_earned_charge + $second_earned_charge;
            $app_earned_charge = $earned_charge - $lose;

            //gameWallet
            $balance = GameWallet::query();
            $balanceDollar = GameChargeHistory::query();
            if (request("date") != null) {
                $date = request("date");
                $year = substr($date, 0, 4);
                $month = substr($date, 5, 2);
                $balance = $balance->whereMonth("created_at", $month)->whereYear("created_at", $year);
                $balanceDollar = $balanceDollar->whereMonth("created_at", $month)->whereYear("created_at", $year);
            } else {
                $balance = $balance->whereMonth("created_at", date("m"))->whereYear("created_at", date("Y"));
                $balanceDollar = $balanceDollar->whereMonth("created_at", date("m"))->whereYear("created_at", date("Y"));
            }
            $balance = @$balance->first();
            $balanceDollar = $balanceDollar->sum("value");
            $allBalance = $balance->balance ?? 0;
            $availableBalance = $balance ? $balance->balance - $balance->used : 0;
            $data = [$balance->used ?? 0, $availableBalance ?? 0];
            $user = Auth::user();
            $usePercentage = ($balance->balance  ?? 0 >= 0) ? (($balance->used ?? 0 / $balance->balance) * 100) : 0;
            return $content
                ->title(trans('statistics'))
                ->description(__(request('desc') ?: 'الرئيسيه'))

                ->row(function (\Encore\Admin\Layout\Row $row) use ($user,$data, $balanceDollar,$allBalance,$usePercentage ) {
                    if ($user->isRole('admin') || $user->isRole('developer')) {
                        $row->column(12, view('admin.dashboard.chart', compact("data", 'balanceDollar', 'allBalance','usePercentage')));
                    }
                })
                ->row(function (\Encore\Admin\Layout\Row $row) use ($onlineUsers, $allUsersCount) {

                    $row->column(12, '<h3 style="color: #000; font-family: \'Arial\', sans-serif;"><i class="fa fa-star"></i> ' . __('Users') . ' <i class="fa fa-star"></i></h3>');
                    $row->column(6, new InfoBox(__('Number of users'), 'dollar', 'green', route('admin.users'), number_format(@$allUsersCount ?? 0)));
                    $row->column(6, new InfoBox(__('Online Users'), 'dollar', 'green', route('admin.users', ['online' => 1]), number_format(@$onlineUsers ?? 0)));
                })
                ->row(function (\Encore\Admin\Layout\Row $row) use ($coins, $total_monthly_di_recieved) {

                    $row->column(12, '<h3 style="color: #000; font-family: \'Arial\', sans-serif;"><i class="fa fa-star"></i> ' . __('Total Coins') . ' <i class="fa fa-star"></i></h3>');

                    $row->column(6, new InfoBox(__('total coins'), 'dollar', 'green', route('admin.users', ['have_coins' => 1]), number_format(@$coins ?? 0)));
                    $row->column(6, new InfoBox(__('totalDiamond'), 'dollar', 'yellow', route('admin.users', ['have_coins' => 1]), number_format(@$total_monthly_di_recieved ?? 0)));
                })
                ->row(function (\Encore\Admin\Layout\Row $row) use ($user_sallaries, $agency_sallaries) {
                    $row->column(12, '<h3 style="color: #000; font-family: \'Arial\', sans-serif;"><i class="fa fa-star"></i> ' . __('salaries') . ' <i class="fa fa-star"></i></h3>');

                    $row->column(6, new InfoBox(__('users sallaries'), 'dollar', 'blue', route('admin.sallaries', ['name' => 'users', 'salary_only' => 1]), @$user_sallaries ?? 0));
                    $row->column(6, new InfoBox(__('agency sallaries'), 'dollar', 'yellow', route('admin.sallaries', ['name' => 'agencies', 'salary_only' => 1]), @$agency_sallaries ?? 0));
                })->row(function (\Encore\Admin\Layout\Row $row) use ($total_sallary) {
                    $row->column(12, '<h3 style="color: #000; font-family: \'Arial\', sans-serif;"><i class="fa fa-star"></i> ' . __('total salaries') . ' <i class="fa fa-star"></i></h3>');

                    $row->column(6, new InfoBox(__('total salaries'), 'dollar', 'black', '', @$total_sallary ?? 0));
                })->row(function (\Encore\Admin\Layout\Row $row) use ($app_earned_charge) {
                    $row->column(12, '<h3 style="color: #000; font-family: \'Arial\', sans-serif;"><i class="fa fa-star"></i> ' . __('app earned') . ' <i class="fa fa-star"></i></h3>');

                    $row->column(6, new InfoBox(__('app earned'), 'dollar', 'yellow', route('admin.app-earned'), @$app_earned_charge ?? 0));
                });
        } else {
            return $content
                ->title(trans('statistics'))
                ->description(__(request('desc') ?: __("Main")));
        }
    }


    public function appInformation()
    {
        $allUsersCount = User::query()->count() ?? 0;
        $onlineUsers = DB::table('users')->selectRaw('device_token')->where('online_time', '>=', now()->startOfDay()->timestamp)->where('online_time', '<=', now()->timestamp)->groupBy(['device_token'])->get()->count() ?? 0;
        $coins  = User::sum("di") ?? 0;
        $total_monthly_di_recieved  = User::sum("monthly_diamond_received") ?? 0;
        $user_sallaries   = UserSallary::query()->whereHas('user', function ($q) {
            $q->where('agency_id', '!=', 0);
        })->sum(DB::raw('sallary - cut_amount')) ?? 0;
        $agency_sallaries = AgencySallary::query()->sum(DB::raw('sallary - cut_amount')) ?? 0;
        $total_sallary    = $user_sallaries + $agency_sallaries;
        $lose1 = UsdTransfer::whereMonth("created_at", date("m"))->whereYear("created_at", date("Y"))->sum("value");
        $lose2 = RequestTakeSalary::where("status", 1)->whereMonth("created_at", date("m"))->whereYear("created_at", date("Y"))->sum("amount");
        $lose = $lose1 + $lose2;
        $first_earned_charge = Charge::where("charger_type", "dash")->whereMonth("created_at", date("m"))->whereYear("created_at", date("Y"))->sum("usd");
        $second_earned_charge = CoinLog::whereIn('method', ['huawei_pay', 'google_pay', 'apple_pay'])->whereMonth("created_at", date("m"))->whereYear("created_at", date("Y"))->sum("paid_usd");
        $earned_charge = $first_earned_charge + $second_earned_charge;
        $app_earned_charge = $earned_charge - $lose;
        $data = [
            'allUsersCount' =>number_format( $allUsersCount) ??0,
            'onlineUsers' => number_format($onlineUsers) ?? 0,
            'coins' => number_format($coins) ?? 0,
            'diamonds' => number_format($total_monthly_di_recieved )?? 0,
            'user_salaries' =>number_format($user_sallaries) ?? 0,
            'agency_salaries' => number_format($agency_sallaries) ?? 0,
            'total_salaries' =>number_format($total_sallary) ?? 0 ,
            'app_earned' => number_format($app_earned_charge )?? 0,
        ];
        return Common::apiResponse(1, '', $data);
    }
}
