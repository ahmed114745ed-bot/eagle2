<?php

namespace Modules\AreaManager\Actions;

use Cache;
use App\Models\Charge;
use App\Helpers\Common;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Models\ChargeInvoice;
use App\Enums\UserCoinLogType;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Actions\Action;
use App\Helpers\UserCoinLogHelper;
use Encore\Admin\Actions\Response;
use Illuminate\Support\Facades\DB;
use App\Enums\Charges\UserTypeEnum;
use Illuminate\Support\Facades\Auth;
use Modules\SuperAdmin\Entities\SuperAdmin;
use Illuminate\Validation\ValidationException;

class SuperAdminChargeAction extends Action
{
    public $name;
    protected $selector = '.charge_action';
    protected $userId;

    public function setUserId($userId): static
    {
        $this->userId = $userId ?? request()->input('userId');
        return $this;
    }

    /**
     * @throws \Throwable
     */
    public function handle(Request $request)
    {
        $userId = $this->userId ?? $request->input('userId');
        $superAdmin = $this->getSuperAdmin($userId);

        return $this->handleUserCharge($request, $superAdmin);
    }

    private function getSuperAdmin($userId)
    {
        return SuperAdmin::where('id', $userId)->first();
    }

    /**
     * @throws \Throwable
     */
    private function handleUserCharge(Request $request, SuperAdmin $superAdmin): Response
    {
        $amount = $request->charge_type == 'increment' ? $request->amount : -$request->amount;
        $authAreaManager = auth()->user();
        $typeCharge = $request->charge_type;

        $userCoins = \App\Services\CoinRateService::getEffectiveRate($authAreaManager);

        if ($request->amount_unit === 'usd') {
            $coins = $amount * $userCoins;
        } else {
            $coins = $amount;
        }

        $extraCoins = 0;
        $totalCoins = $coins;

        if ($typeCharge === 'increment') {
            if ($authAreaManager->di < abs($totalCoins)) {
                return $this->response()->error(__('You do not have enough coins, please recharge!'))->refresh();
            }
        }

        if ($typeCharge === 'decrement' && $superAdmin->di < abs($coins)) {
            return $this->response()->error(__('Insufficient user balance'))->refresh();
        }

        if (!$userCoins || $userCoins == 0) {
            return $this->response()->error(__('please set app coin rate in settings'))->refresh();
        }

        DB::transaction(function () use ($request, $superAdmin,  $amount, $coins, $extraCoins, $totalCoins, $typeCharge, $authAreaManager) {

            $amountBefore = $superAdmin->di; //Common::getCurrentBalance($superAdmin->id);

            UserCoinLogHelper::logByType(
                $superAdmin->id,
                $typeCharge == 'increment' ? $totalCoins : -$coins,
                $amountBefore,
                UserCoinLogType::ADMIN_CHARGES,
                userType: UserTypeEnum::SUPER_ADMIN,
            );

            $superAdmin->di += $typeCharge == 'increment' ? $totalCoins : -$coins;
            if ($superAdmin->di < 0) {
                throw ValidationException::withMessages([
                    'di' => [__('user does not have this coin')],
                ]);
            }

            $superAdmin->save();

            if ($request->charge_type == 'increment') {
                $authAreaManager->di -= abs($totalCoins);
                $authAreaManager->save();
            }

            if ($request->charge_type == 'decrement') {
                $authAreaManager->di += abs($coins);
                $authAreaManager->save();
            }

            $this->createChargeRecord($request,  $superAdmin, $amount, $coins, $extraCoins, $totalCoins, $request->amount);
        });

        return $this->response()->success('Success')->refresh();
    }

    private function createChargeRecord(Request $request, SuperAdmin $superAdmin, $amount, $coins, $extraCoins, $totalCoins, $usdAmount): void
    {
        $charge = new Charge();
        $charge->charger_id = Auth::id();
        $charge->charger_type = UserTypeEnum::AREA_MANAGER;
        $charge->user_id = $superAdmin->id;
        $charge->agency_id = null;
        $charge->user_type = UserTypeEnum::SUPER_ADMIN;
        $charge->base_coin_rate = \App\Services\CoinRateService::getEffectiveRate(Auth::user());
        $charge->amount = $request->charge_type == 'increment' ? $totalCoins : -$coins;
        $charge->usd = $request->amount_unit === 'usd' ? $usdAmount : $usdAmount / $charge->base_coin_rate;
        $charge->balance_before = $superAdmin->di - ($request->charge_type == 'increment' ? $totalCoins : -$coins);

        $charge->sent_coins = $request->charge_type == 'increment' ? $coins : -$coins;
        $charge->extra_coins = $request->charge_type == 'increment' ? $extraCoins : 0;
        $charge->total_coins = $request->charge_type == 'increment' ? $totalCoins : -$coins;
        $charge->transaction_type = 'super_admin_charge';

        $charge->save();

        if ($request->hasFile('invoice')) {
            $imagePath = Common::upload('profile', $request->file('invoice'));
        }

        ChargeInvoice::create([
            'charge_id' => $charge->id,
            'user_id' => $superAdmin->id,
            'reason_en' => $request->reason_en,
            'reason_ar' => $request->reason_ar,
            'invoice' => $imagePath ?? '',
            'type' => UserTypeEnum::SUPER_ADMIN,
        ]);
    }

    public function form(): void
    {
        $this->name = __('Charge');
        $this->hidden('userId')->attribute('id', 'vid');
        $this->select('charge_type', __('Charge Type'))->options(['increment' => __('increment'), 'decrement' => __('decrement')])->default('increment');

        $this->hidden('coin_rate')->value(\App\Services\CoinRateService::getEffectiveRate(auth()->user()))->attribute(['id' => 'coin-rate']);

        $this->select('amount_unit', __('Amount Unit'))
            ->options([
                'usd'   => __('Dollar'),
                'coins' => __('Coins'),
            ])
            ->default('usd')
            ->attribute(['id' => 'amount-unit-select']);

        $this->text('amount', __('Amount'))
            ->rules('numeric|gt:0')
            ->attribute(['id' => 'amount-input'])
            ->help('<span id="amount-conversion-help" style="color: #28a745; font-weight: bold;"></span>');

        $this->text('reason_en', __('reason en'));
        $this->text('reason_ar', __('reason ar'));

        $this->select('form', __('add invoice'))
            ->options([
                0 => __('no'),
                1 => __('yes'),
            ])
            ->attribute(['id' => 'form-select']);

        $this->image('invoice', __('invoice'))
            ->attribute([
                'id' => 'invoice-field',

            ]);

        $this->hidden('amount_type')->value(1);

        $transEquivalent = __('Equivalent:');
        $transCoins = __('Coins');
        $transUsd = __('Dollar');

        Admin::script("
            var transEquivalent = '{$transEquivalent}';
            var transCoins = '{$transCoins}';
            var transUsd = '{$transUsd}';
        " . <<<'SCRIPT'
            function toggleInvoiceField() {
                var selected = $('#form-select').val();
                if (selected === '1') {
                    $('#invoice-field').closest('.form-group').show();
                } else {
                    $('#invoice-field').closest('.form-group').hide();
                }
            }
            
            function updateConversions() {
                var rate = parseFloat($('#coin-rate').val()) || 1;
                var amount = parseFloat($('#amount-input').val()) || 0;
                var unit = $('#amount-unit-select').val();
                
                var baseCoins = 0;
                var baseUsd = 0;
                
                if (unit === 'usd') {
                    baseCoins = amount * rate;
                    baseUsd = amount;
                    $('#amount-conversion-help').text(transEquivalent + ' ' + baseCoins.toLocaleString() + ' ' + transCoins);
                } else {
                    baseCoins = amount;
                    baseUsd = amount / rate;
                    $('#amount-conversion-help').text(transEquivalent + ' ' + baseUsd.toFixed(2) + ' ' + transUsd);
                }
            }

            $(document).off('change', '#form-select').on('change', '#form-select', toggleInvoiceField);
            
            $(document).on('input', '#amount-input', updateConversions);
            $(document).on('change', '#amount-unit-select', updateConversions);
            
            toggleInvoiceField();
            updateConversions();
        SCRIPT);
    }


    public function html(): string
    {
        $title = __('dashboard.add_coins');
        $shippingReports = __('Charge reports');
        $url = url('areaManager/superadmin-charges-report/' . $this->userId . '?name=area_manager');

        $html = '';

        $html .= '<a href="javascript:void(0);" onclick="pu(' . $this->userId . ')" class="charge_action btn btn-sm text-white" style="background-color: #28a745; border-color: #28a745; color: white;">'
            . htmlspecialchars($title) .
            '</a>';

        $html .= '<a href="' . htmlspecialchars($url) . '"
        class="shipping_report btn btn-sm text-white"
        onclick="initDatePickersAfterNav()"
        style="background-color: #b93a0f; border-color: #b93a0f; color: white;">'
            . htmlspecialchars($shippingReports) .
            '</a>';

        $html .= <<<HTML
            <script>
            function pu(val) {
                $("#vid").val(val);
            }

            function initDatePickersAfterNav() {
                setTimeout(function() {
                    $('.form-control[id$="_date"]').datetimepicker({
                        format: 'YYYY-MM-DD'
                    });
                }, 500);
            }

            $(document).on('pjax:complete', function() {
                initDatePickersAfterNav();
            });
            </script>
            HTML;

        return $html;
    }
}
