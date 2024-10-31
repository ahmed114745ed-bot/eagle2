<?php

namespace App\Admin\Controllers;


use Carbon\Carbon;
use App\Models\Charge;

use App\Models\CoinLog;
use App\Models\AppFeature;

use App\Models\Commission;
use App\Models\UsdTransfer;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Form;
use Encore\Admin\Layout\Content;


use App\Models\RequestTakeSalary;
use Encore\Admin\Widgets\InfoBox;
use Encore\Admin\Widgets\DatePicker;
use Illuminate\Support\Facades\Request;


class AppEarnedController extends MainController
{

    public function index(Content $content)
    {
        $start_date =request('start_date') != null? Carbon::createFromFormat('Y-m-d',request('start_date'))->startOfDay():Carbon::now()->startOfMonth()->startOfDay();
        $end_date = request('start_date') != null?Carbon::createFromFormat('Y-m-d',request('end_date'))->endOfDay():Carbon::now()->endOfMonth()->endOfDay();
        $lose1 = UsdTransfer::whereBetween('created_at',[ $start_date, $end_date ])->sum("value");
            $lose2 = RequestTakeSalary::where("status",1)->whereBetween('created_at',[ $start_date, $end_date ])->sum("amount");
            $lose2 = $lose2 ;
            $lose = $lose1 + $lose2;
            $first_earned_charge = Charge::where("charger_type","dash")->whereBetween('created_at',[ $start_date, $end_date ])->sum("usd");
            $second_earned_charge = CoinLog::whereIn('method',['huawei_pay','google_pay','apple_pay' ])->whereBetween('created_at',[ $start_date, $end_date ])->sum("paid_usd");
            $first_earned = Charge::where("charger_type","dash")->sum("usd");
            $second_earned = CoinLog::whereIn('method',['huawei_pay','google_pay','apple_pay' ])->sum("paid_usd");
            $earned = $first_earned + $second_earned;
            $earned_charge = $first_earned_charge + $second_earned_charge;
            $app_earned_charge = $earned_charge - $lose ;

            $bonus = $earned * 0.1;
            $commission = Commission::sum("amount");
            $deduction = number_format($bonus- $commission, 2) ;
            $appFeature = AppFeature::where(['slug' => 'commission','status' => 1])->exists();
            return $content
                ->title(trans('app-earned'))
                ->description(__( 'الرئيسيه'))
                ->row(function ($row) {
                    $row->column(12, view('admin.grid.common.date'));})
                // ->row(function (\Encore\Admin\Layout\Row $row) use ( $lose1,$lose2) {

                //     $row->column(12, '<h3 style="color: #000; font-family: \'Arial\', sans-serif;"><i class="fa fa-star"></i> ' . __('lose') . ' <i class="fa fa-star"></i></h3>');
                //     $row->column(6, new InfoBox(__('usdTransfer'), 'dollar', 'green','', number_format(@$lose1 ?? 0)));
                //     $row->column(6, new InfoBox(__('salaryRequest'), 'dollar', 'green', '', number_format(@$lose2 ?? 0)));

                // })
                ->row(function (\Encore\Admin\Layout\Row $row) use ($first_earned_charge,$second_earned_charge) {

                  //  $row->column(12, '<h3 style="color: #000; font-family: \'Arial\', sans-serif;"><i class="fa fa-star"></i> ' . __('earned') . ' <i class="fa fa-star"></i></h3>');

                    $row->column(6, new InfoBox(__('chargeChanges'), 'dollar', 'green', route('admin.charges-details'), number_format(@$first_earned_charge ?? 0)));
                    $row->column(6, new InfoBox(__('coinsGets'), 'dollar', 'yellow', '', number_format(@$second_earned_charge ?? 0)));

                })

                ->row(function (\Encore\Admin\Layout\Row $row) use (  $earned_charge,$bonus,$deduction ,$appFeature) {
                   // $row->column(12, '<h3 style="color: #000; font-family: \'Arial\', sans-serif;"><i class="fa fa-star"></i> ' . __('app earned') . ' <i class="fa fa-star"></i></h3>');

                    $row->column(6, new InfoBox(__('app earned'), 'dollar', 'yellow', '', @$earned_charge ?? 0));
                    if($appFeature) $row->column(6, new InfoBox(__('bonus'), 'dollar', 'yellow', '',@$deduction ?? 0));
                   
                })->row(function (\Encore\Admin\Layout\Row $row) use ($commission,$appFeature) {
                   if($appFeature)  $row->column(6, new InfoBox(__('commission'), 'dollar', 'red',route('admin.commissions'), @$commission ?? 0));
                 });


    }
}
