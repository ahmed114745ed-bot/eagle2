<?php

namespace App\Admin\Actions;

use App\Models\User;
use App\Models\Agency;
use App\Models\Charge;
use Encore\Admin\Form;
use App\Helpers\Common;
use App\Helpers\UserCommon;
use Illuminate\Http\Request;
use Encore\Admin\Actions\Action;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\Achievement\Http\Services\UserAchievementService;
use App\Facades\CustomNotification;

class ChargeAction extends Action
{
    public $name;
    protected $selector = '.charge_action';
    protected $agencyId;

    public function setAgencyId($agencyId): static
    {
        $this->agencyId = $agencyId;
        return $this;
    }

    public function handle(Request $request)
    {
//        if ($request->user_type != 'dash') {
//            $user = $this->getUser($request);
//            if (!$user) {
//                return $this->response()->error(__('user not found'))->refresh();
//            }
//
//            if ($this->isInvalidAmount($request->amount)) {
//                return $this->response()->error(__('amount must be more than 10'))->refresh();
//            }
//        }

//        if ($request->user_type == 'dash') {

        $agency = $this->getAgency($request->agency_id);
        if (!$agency) {
            return $this->response()->error(__('api_responses.agency'))->refresh();
        }
        if ($agency->is_frozen == 1) {
            return $this->response()->error(__('api_responses.frozen'))->refresh();
        }
        $user = $agency->owner;
        return $this->handleAgencyCharge($request, $agency, $user);

//        }
//        return $this->handleUserCharge($request, $user);
    }

    private function getUser(Request $request)
    {
//        if ($request->user_type == 'dashdash') {
//            return $request->id_type == '1'
//                ? User::query()->searchByUuid($request->user_id)->first()
//                : User::query()->find($request->user_id);
//        }

        return $request->id_type == '1'
            ? User::query()->where('uuid', $request->user_id)->first()
            : User::query()->find($request->user_id);
    }

    private function getAgency($agencyId)
    {
        return Agency::where("id", $agencyId)->first();
    }

    private function isInvalidAmount($amount)
    {
        return $amount < 10;
    }

    private function handleAgencyCharge(Request $request, Agency $agency, User $user)
    {
        $amount = $request->charge_type == 'increment' ? $request->amount : -$request->amount;
        if ($amount < 0 && $agency->coins < abs($amount)) {
            return $this->response()->error(__('Insufficient agency balance'))->refresh();
        }

        DB::transaction(function () use ($request, $agency, $user, $amount) {
            $agency->coins += $amount;
            $agency->save();

            $this->createChargeRecord($request, $user, $agency, $amount);

            if ($request->charge_type == "increment") {
                CustomNotification::chargeAction($user, $request);
            }
        });

        return $this->response()->success('Success')->refresh();
    }

    private function handleUserCharge(Request $request, User $user)
    {
        $percentage = Common::getConf("special_transfer_to_usd") ?? 1;
        $usdAmount = $request->amount / $percentage;

        DB::transaction(function () use ($request, $user, $usdAmount) {
            $amount = $request->charge_type == 'increment' ? $request->amount : -$request->amount;
            if ($amount < 0 && $user->di < abs($amount)) {
                return $this->response()->error(__('Insufficient user balance'))->refresh();
            }

            $user->di += $amount;
            $user->save();
            if ($request->charge_type == "increment") {
                CustomNotification::chargeAction($user, $request);
            }
            $this->createChargeRecord($request, $user, null, $amount, $usdAmount);
            (new UserAchievementService())->insertCharging($user, $request->amount);
        });

        return $this->response()->success('Success')->refresh();
    }

    private function createChargeRecord(Request $request, User $user, ?Agency $agency, $amount, $usdAmount = 0)
    {
        $charge = new Charge();
        $charge->charger_id = Auth::id();
        $charge->charger_type = 'agency';
        $charge->user_id = $agency->id;
        $charge->agency_id = $agency->id ?? null;
        $charge->user_type = $request->user_type ?? 'app';
        $charge->amount = $amount;
        $charge->usd = $usdAmount;
        $charge->balance_before = ($agency ? $agency->coins : $user->di) - $amount;
        //dd($charge);
        $charge->save();

        UserCommon::UserEarnedInvitation($user->id, $amount);
    }

    public function form()
    {
        $this->name = __('Charge');
        $this->hidden('agency_id')->value($this->agencyId);
        // $this->hidden('charger_type')->value('dash');
//        $this->text('user_id', __('User ID / Agency ID'));
//        $this->select('id_type', __('ID Type'))->options([0 => __('Normal'), 1 => __('Uuid')]);
        $this->select('charge_type', __('Charge Type'))->options(['increment' => __('increment'), 'decrement' => __('decrement')])->default('increment');
//        $this->select('user_type', __('User Type'))->options(['dashdash' => __('App'), 'dash' => __('Agencies')])->default('dashdash'); //dashdash
        $this->text('amount', __('Amount'));
        $this->hidden('amount_type')->value(1);
    }

    public function html()
    {
        $title = __('dashboard.add_coins');
        $shippingReports = __('Charge reports');
        $url = url('admin/charge-reports/' . $this->agencyId);
        return <<<HTML
            <a href="javascript:void(0);" class="charge_action btn btn-sm  text-white" style="background-color: #28a745; border-color: #28a745; color: white;">{$title} </a>
            <a href="{$url}" class="shipping_report btn btn-sm  text-white" style="background-color: #b93a0f; border-color: #b93a0f; color: white;">{$shippingReports} </a>
HTML;
    }

}
