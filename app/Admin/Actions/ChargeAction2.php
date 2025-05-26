<?php

namespace App\Admin\Actions;

use App\Models\Charge;
use App\Models\Setting;
use App\Models\ShippingAgency;
use Illuminate\Http\Request;
use Encore\Admin\Actions\Action;
use Illuminate\Support\Facades\DB;
use App\Facades\CustomNotification;
use Illuminate\Support\Facades\Auth;
use Encore\Admin\Facades\Admin;
use Illuminate\Validation\ValidationException;

class ChargeAction2 extends Action
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
        $agency = $this->getAgency($request->agency_id);
        if (!$agency) {
            return $this->response()->error(__('api_responses.agency'))->refresh();
        }
        if ($agency->is_frozen == 1) {
            return $this->response()->error(__('frozen'))->refresh();
        }
        return $this->handleAgencyCharge($request, $agency);
    }


    private function getAgency($agencyId)
    {
        return ShippingAgency::where("id", $agencyId)->first();
    }

    private function handleAgencyCharge(Request $request, ShippingAgency $agency)
    {
        $amount = $request->charge_type == 'increment' ? $request->amount : -$request->amount;

        if ($amount < 0 && $agency->coins < abs($amount)) {
            return $this->response()->error(__('Insufficient agency balance'))->refresh();
        }
        $shippingCoins = \Cache::rememberForever('shipping_coins', function () {
            $setting =   Setting::where('key', 'shipping_coins')->first();
            return $setting?->value;
        });
        if (! $shippingCoins || $shippingCoins == 0) {
            return $this->response()->error(__('please set agency coins in configs'))->refresh();
        }

        DB::transaction(function () use ($request, $agency,  $amount, $shippingCoins) {
            $coins = $amount * $shippingCoins;

            $agency->coins += $coins;
            if ($agency->coins < 0) {
                throw ValidationException::withMessages([
                    'coins' => [__('agency does not have this coin')],
                ]);
            }
            $agency->save();

            $this->createChargeRecord($request,  $agency, $amount, $coins, $request->amount);

            if ($request->charge_type == "increment") {
                $admin = Auth::user()->username ?? 'Admin';
                if ($agency->owner) CustomNotification::chargeAction($agency->owner, $request, $admin);
            }
        });

        return $this->response()->success('Success')->refresh();
    }



    private function createChargeRecord(Request $request, ShippingAgency $agency, $amount, $coins = 0, $usdAmount)
    {

        $charge = new Charge();
        $charge->charger_id = Auth::id();
        $charge->charger_type = $request->user_type == 'dash' ? 'dash' : 'dash';
        $charge->user_id = $agency->id;
        $charge->agency_id = $agency->id ?? null;
        $charge->user_type = 'agency';
        $charge->amount = $coins;
        $charge->usd = $usdAmount;
        $charge->balance_before =  $agency->coins  - $amount;
        $charge->save();
    }

    public function form()
    {
        $this->name = __('Charge');
        $this->hidden('agency_id')->attribute('id', 'vid');
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
        $url = url('admin/charge-reports/' . $this->agencyId);

        $html = '';

        if (Admin::user()->can('add-coins-Switch') || Admin::user()->can('*')) {
            $html .= '<a href="javascript:void(0);" onclick="pu(' . $this->agencyId . ')" class="charge_action btn btn-sm text-white" style="background-color: #28a745; border-color: #28a745; color: white;">'
                . htmlspecialchars($title) .
                '</a>';
        }

        if (Admin::user()->can('charge-report-Switch') || Admin::user()->can('*')) {
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
