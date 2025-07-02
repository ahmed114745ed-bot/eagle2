<?php

namespace App\Admin\Actions;

use App\Models\Charge;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Encore\Admin\Actions\Action;
use Illuminate\Support\Facades\DB;
use App\Facades\CustomNotification;
use Illuminate\Support\Facades\Auth;
use Encore\Admin\Facades\Admin;
use Illuminate\Validation\ValidationException;

class UsersChargeAction extends Action
{
    public $name;
    protected $selector = '.charge_action';
    protected $userId;

    public function setUserId($userId ): static
    {
        $this->userId = $userId ?? request()->input('userId');
        return $this;
    }

    public function handle(Request $request)
    {    $userId = $this->userId ?? $request->input('userId');
        $user = $this->getUser($userId);
        // if (!$user) {
        //     return $this->response()->error(__('api_responses.agency'))->refresh();
        // }
        // if ($user->is_frozen == 1) {
        //     return $this->response()->error(__('frozen'))->refresh();
        // }
        return $this->handleUserCharge($request, $user);
    }


    private function getUser($userId)
    {
        return User::where('id', $userId)->first();
    }

    private function handleUserCharge(Request $request, User $user)
    {
        $amount = $request->charge_type == 'increment' ? $request->amount : -$request->amount;

        if ($amount < 0 && $user->di < abs($amount)) {
            return $this->response()->error(__('Insufficient user balance'))->refresh();
        }
        $userCoins = \Cache::rememberForever('user_coins', function () {
            $setting =   Setting::where('key', 'user_coins')->first();
            return $setting?->value;
        });
        if (! $userCoins || $userCoins == 0) {
            return $this->response()->error(__('please set user coins in configs'))->refresh();
        }

        DB::transaction(function () use ($request, $user,  $amount, $userCoins) {
            $coins = $amount * $userCoins;

            $user->di += $coins;
            if ($user->di < 0) {
                throw ValidationException::withMessages([
                    'di' => [__('user does not have this coin')],
                ]);
            }
            $user->save();

            $this->createChargeRecord($request,  $user, $amount, $coins, $request->amount);

            if ($request->charge_type == "increment") {
                $admin = Auth::user()->username ?? 'Admin';
                if ($user->owner) CustomNotification::chargeAction($user, $request, $admin);
            }
        });

        return $this->response()->success('Success')->refresh();
    }



    private function createChargeRecord(Request $request, User $user, $amount, $coins = 0, $usdAmount)
    {

        $charge = new Charge();
        $charge->charger_id = Auth::id();
        $charge->charger_type = $request->user_type == 'dash' ? 'dash' : 'dash';
        $charge->user_id = $user->id;
        $charge->agency_id =   null;
        $charge->user_type = 'user';
        $charge->amount = $coins;
        $charge->usd = $usdAmount;
        $charge->balance_before =  $user->di  - $coins;
        $charge->save();
    }

    public function form()
    {
        $this->name = __('Charge');
        $this->hidden('userId')->attribute('id', 'vid');
        $this->select('charge_type', __('Charge Type'))->options(['increment' => __('increment'), 'decrement' => __('decrement')])->default('increment');
        $this->text('amount', __('Amount'))
            ->addElementClass('price-input')
            ->help(__('Enter amount in dollars'));
        $this->hidden('amount_type')->value(1);
    }

    public function html()
    {
        $title = __('dashboard.add_coins');
        $shippingReports = __('Charge reports');
        $url = url('admin/user-charges-report/' . $this->userId);

        $html = '';

        if (Admin::user()->can('add-switch-charge-to-user') || Admin::user()->can('*')) {
            $html .= '<a href="javascript:void(0);" onclick="pu(' . $this->userId . ')" class="charge_action btn btn-sm text-white" style="background-color: #28a745; border-color: #28a745; color: white;">'
                . htmlspecialchars($title) .
                '</a>';
        }

        if (Admin::user()->can('history-switch-charge-to-user') || Admin::user()->can('*')) {
            $html .= '<a href="' . htmlspecialchars($url) . '" class="shipping_report btn btn-sm text-white" style="background-color: #b93a0f; border-color: #b93a0f; color: white;">'
                . htmlspecialchars($shippingReports) .
                '</a>';
        }

        $html .= <<<HTML
<script>
function pu(val) {
    $("#vid").val(val);
}
</script>
HTML;

        return $html;
    }
}
