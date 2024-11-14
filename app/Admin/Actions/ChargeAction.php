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

    public function handle(Request $request)
    {
        if ($request->user_type != 'dash') {
            $user = $this->getUser($request);
            if (!$user) {
                return $this->response()->error(__('user not found'))->refresh();
            }

            if ($this->isInvalidAmount($request->amount)) {
                return $this->response()->error(__('amount must be more than 10'))->refresh();
            }
        }

        if ($request->user_type == 'dash') {
            $agency = $this->getAgency($request->user_id);
            if (!$agency) {
                return $this->response()->error(__('api_responses.agency'))->refresh();
            }
            $user = $agency->owner;
            return $this->handleAgencyCharge($request, $agency, $user);
        }

        return $this->handleUserCharge($request, $user);
    }

    private function getUser(Request $request)
    {
        if ($request->user_type == 'dashdash') {
            return $request->id_type == '1'
                ? User::query()->searchByUuid($request->user_id)->first()
                : User::query()->find($request->user_id);
        }

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

            $this->createChargeRecord($request, $user, null, $amount, $usdAmount);
            (new UserAchievementService())->insertCharging($user, $request->amount);
        });

        return $this->response()->success('Success')->refresh();
    }

    private function createChargeRecord(Request $request, User $user, ?Agency $agency, $amount, $usdAmount = 0)
    {
        $charge = new Charge();
        $charge->charger_id = Auth::id();
        $charge->charger_type = $request->user_type == 'dash' ? 'dash' : 'dash';
        $charge->user_id = $user->id;
        $charge->agency_id = $agency->id ?? null;
        $charge->user_type = $request->user_type == 'dash' ? 'dash' : 'app';
        $charge->amount = $amount;
        $charge->usd = $usdAmount;
        $charge->balance_before = ($agency ? $agency->coins : $user->di) - $amount;
        $charge->save();
    }

    public function form()
    {
        $this->name = __('Charge');
        $this->hidden('charger_id')->value(Auth::id());
        // $this->hidden('charger_type')->value('dash');
        $this->text('user_id', __('User ID'));
        $this->select('id_type', __('ID Type'))->options([0 => __('Normal'), 1 => __('Uuid')]);
        $this->select('charge_type', __('Charge Type'))->options(['increment' => __('increment'), 'decrement' => __('decrement')])->default('increment');
        $this->select('user_type', __('User Type'))->options(['app' => __('App'), 'dash' => __('Agencies')])->default('app');//dashdash
        $this->text('amount', __('Amount'));
        $this->hidden('amount_type')->value(1);
    }

    public function html()
    {
        return <<<HTML
            <li><a href="javascript:void(0);" class="charge_action"><i class="fa fa-dollar text-red"></i> إضافة رصيد</a></li>
HTML;
    }
}
