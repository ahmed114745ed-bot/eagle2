<?php

namespace App\Admin\Actions;

use App\Models\User;
use App\Models\Admin;
use App\Models\Agency;
use App\Models\Charge;
use Encore\Admin\Form;
use App\Helpers\Common;
use App\Models\CoinLog;
use App\Helpers\UserCommon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Encore\Admin\Actions\Action;
use Illuminate\Support\Facades\DB;
use App\Facades\CustomNotification;
use Illuminate\Support\Facades\Auth;
use Modules\Achievement\Http\Services\UserAchievementService;

class ChargeAction extends Action
{
    public $name;

    protected $selector = '.charge_action';

    public function handle(Request $request)
    {
        if ($request->user_type == 'app'){
            if ($request->id_type == '1'){
                $user = User::query ()->searchByUuid($request->user_id)->first();
            }else{
                $user = User::query ()->find ($request->user_id);
            }
            if (!$user){
                return $this->response()->error(__('user not found'))->refresh();
            }
            // $agency = Agency::where('app_owner_id',$user->id )->first();

            // if($agency && $agency->status == 0){
            //     return $this->response()->error( __('api_responses.canNotCharge'))->refresh();
            // }

        }elseif ($request->user_type == 'dash'){
            $user = Admin::query ()->find ($request->user_id);
            if (!$user){
                return $this->response()->error(__('user not found'))->refresh();
            }
        }else{
            return $this->response()->error(__('system need to know what type of user you want add balance to'))->refresh();
        }

        if ($request->amount < 10){
            return $this->response()->error(__('amount must be more than 10'))->refresh();
        }

        $charger = Auth::user ();
        DB::beginTransaction ();
        try {
            $percentage = Common::getConf("special_transfer_to_usd") ?? 1;
            $usd =  $request->amount / $percentage ;

            $charge = new Charge();
            $charge->charger_id = Auth::id ();
            $charge->charger_type = 'dash';
            $charge->user_id = $user->id;
            $charge->user_type = $request->user_type;
            $charge->amount_type = 1;
            $charge->balance_before = $user->di;
            if ($request->charge_type == "increment") {
                $user->di += $request->amount;
                $charge->amount = $request->amount;
                $charge->usd += $usd;

            } else {
                if ($request->amount > $user->di) return $this->response()->error(__('messages.coins'))->refresh();
                $user->di -= $request->amount;
                $charge->amount = -$request->amount;
                $charge->usd = 0;

            }
            $charge->save ();
            $user->save ();
            //insert amount to user achievement
            if ($user instanceof User) {
                (new UserAchievementService())->insertCharging($user, $request->amount);
            }
            if (!$charger->isRole('admin') && !$charger->isRole('developer')){
                if($charger->isRole('agency')){
                    $agency = Agency::query ()->where ('owner_id',$charger->id)->first ();
                    if ($agency){
                        $agency_balance = $agency->salary;
                        $usd_coins = Common::getConf ('one_usd_value_in_coins')?:10;
                        $agency_balance_coins = $agency_balance * $usd_coins;
                        if ($agency_balance_coins < $request->amount){
                            return $this->response()->error(__ ('balance not enough'))->refresh();
                        }
                        $amount_usd = $request->amount / $usd_coins;
                        $ta = $agency->target();
                        $ta->cut_amount += $amount_usd;
                        $ta->save();
                        $agency->save ();
                    }else{
                        if ($charger->di < $request->amount){
                            return $this->response()->error(__ ('balance not enough'))->refresh();
                        }
                        $charger->di -= $request->amount;
                        $charger->save();
                    }
                }else{
                    if ($charger->di < $request->amount){
                        return $this->response()->error(__ ('balance not enough'))->refresh();
                    }
                    $charger->di -= $request->amount;
                    $charger->save();
                }
            }
            /*CoinLog::query ()->create (
                [
                    'paid_usd'=>0,
                    'obtained_coins'=>$request->amount,
                    'user_id'=>$user->id,
                    'method'=>@\auth ()->user ()->name?:'agent',
                    'donor_id'=> Auth::id (),
                    'donor_type'=>\auth ()->user ()->roles->first()->name,
                    'status'=>1,
                    'trx'=>$randomString = rand(111111111111111111,999999999999999999)
                ]
            );*/
            DB::commit ();
            UserCommon::UserEarnedInvitation($user->id,$request->amount);
            if ($request->charge_type == "increment") {
                CustomNotification::chargeAction($user, $request);
            }
            return $this->response()->success('success')->refresh();
        }catch (\Exception $exception){
            DB::rollBack ();
            return $this->response()->error($exception->getMessage ())->refresh();
        }

    }

    public function form() 
    {
        $this->name = __ ('Charge');
        $this->hidden('charger_id', 'charger id')->value (Auth::id ());
        $this->hidden('charger_type', 'charger_type')->value ('dash');
        $this->text('user_id', __('user id'));
        $this->select('id_type', __('id type'))->options ([0=>__('normal'),1=>__('big')]);
        $this->select('charge_type', __('charge_type'))->options(['increment' => __('increment'), 'decrement' => __('decrement')])->default('increment');
        $this->select('user_type', __('user type'))->options (['app'=>__ ('app'),
            //  'dash'=>__ ('dash')
        ])->default ('app');
        $this->text('amount', __('amount'));
        $this->hidden('amount_type', 'amount_type')->value (1);
    }

    public function html()
    {
        return <<<HTML

    <li><a href="javascript:void(0);" class="charge_action "><i class="fa fa-dollar text-red"></i>اضافة رصيد</a></li>

HTML;
    }
}
