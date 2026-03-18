<?php

namespace App\Admin\Actions;

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
use Modules\AreaManager\Entities\AreaManager;
use Illuminate\Validation\ValidationException;

class AreaManagerChargeAction extends Action
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
        $areaManager = $this->getAreaManager($userId);

        return $this->handleUserCharge($request, $areaManager);
    }

    private function getAreaManager($userId)
    {
        return AreaManager::where('id', $userId)->first();
    }

    /**
     * @throws \Throwable
     */
    private function handleUserCharge(Request $request, AreaManager $areaManager): Response
    {
        $amount = $request->charge_type == 'increment' ? $request->amount : -$request->amount;
        $typeCharge = $request->charge_type;

        $userCoins = \App\Services\CoinRateService::getAppBaseRate();

        if ($request->amount_unit === 'usd') {
            $coins = $amount * $userCoins;
        } else {
            $coins = $amount;
        }

        $extraCoins = 0;
        if ($typeCharge === 'increment') {
            $cashbackType = $request->cashback_type ?? 'fixed';
            $cashbackVal = abs($request->extra_coins) ?? 0;
            if ($cashbackType === 'percent') {
                $extraCoins = $coins * ($cashbackVal / 100);
            } else {
                $extraCoins = $cashbackVal;
            }
        }
        $totalCoins = $coins + $extraCoins;

        if ($typeCharge === 'decrement' && $areaManager->di < abs($coins)) {
            return $this->response()->error(__('Insufficient user balance'))->refresh();
        }

        DB::transaction(function () use ($request, $areaManager,  $amount, $coins, $extraCoins, $totalCoins, $typeCharge) {

            $amountBefore = $areaManager->di; //Common::getCurrentBalance($areaManager->id);

            UserCoinLogHelper::logByType(
                $areaManager->id,
                $typeCharge == 'increment' ? $totalCoins : -$coins,
                $amountBefore,
                UserCoinLogType::ADMIN_CHARGES,
                userType: UserTypeEnum::AREA_MANAGER,
            );

            $areaManager->di += $typeCharge == 'increment' ? $totalCoins : -$coins;
            if ($areaManager->di < 0) {
                throw ValidationException::withMessages([
                    'di' => [__('user does not have this coin')],
                ]);
            }

            $areaManager->save();

            $this->createChargeRecord($request,  $areaManager, $amount, $coins, $extraCoins, $totalCoins, $request->amount);
        });

        return $this->response()->success('Success')->refresh();
    }

    private function createChargeRecord(Request $request, AreaManager $areaManager, $amount, $coins, $extraCoins, $totalCoins, $usdAmount): void
    {
        $appBaseRate = \App\Services\CoinRateService::getAppBaseRate();
        $effectiveRate = $appBaseRate;

        $baseUsd = $request->amount_unit === 'usd' ? $usdAmount : $usdAmount / $appBaseRate;
        $totalCoins = $coins;

        $calc = \App\Services\ChargeCalculationService::calculate($baseUsd, 'usd', $effectiveRate);

        $baseCoins = $calc['base_coins'];
        $profitCoins = $calc['profit_coins'];
        $profitUsd = $calc['profit_usd'];

        $charge = new Charge();
        $charge->charger_id = Auth::id();
        $charge->charger_type = $request->user_type == 'dash' ? 'dash' : 'dash';
        $charge->user_id = $areaManager->id;
        $charge->agency_id =   null;
        $charge->user_type = UserTypeEnum::AREA_MANAGER;
        $charge->amount = $request->charge_type == 'increment' ? $totalCoins : -$coins;
        $charge->usd = $baseUsd;
        $charge->balance_before =  $areaManager->di  - ($request->charge_type == 'increment' ? $totalCoins : -$coins);

        $charge->total_coins = $request->charge_type == 'increment' ? $totalCoins : -$coins;
        $charge->transaction_type = 'area_manager_charge';

        $charge->rate_source = 'app';
        $charge->applied_coin_rate = $effectiveRate;
        $charge->base_usd = $baseUsd;
        $charge->base_coins = $baseCoins;
        $charge->bonus_coins = $extraCoins;
        $charge->profit_usd = $profitUsd;
        $charge->profit_coins = $profitCoins;

        $charge->save();

        if ($request->hasFile('invoice')) {
            $imagePath = Common::upload('profile', $request->file('invoice'));
        }

        ChargeInvoice::create([
            'charge_id' => $charge->id,
            'user_id' => $areaManager->id,
            'reason_en' => $request->reason_en,
            'reason_ar' => $request->reason_ar,
            'invoice' => $imagePath ?? '',
            'type' => UserTypeEnum::AREA_MANAGER,
        ]);
    }

    public function form(): void
    {
        $this->name = __('Charge');
        $this->hidden('userId')->attribute('id', 'vid');
        $this->select('charge_type', __('Charge Type'))->options(['increment' => __('increment'), 'decrement' => __('decrement')])->default('increment');

        $this->hidden('coin_rate')->value(\App\Services\CoinRateService::getAppBaseRate())->attribute(['id' => 'coin-rate']);

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

        $this->select('cashback_type', __('Cashback Type'))
            ->options([
                'fixed'   => __('Fixed Amount (Coins)'),
                'percent' => __('Percentage (%)'),
            ])
            ->default('fixed')
            ->attribute(['id' => 'cashback-type-select']);

        $this->text('extra_coins', __('Extra Coins (Cashback)'))
            ->rules('nullable|numeric|min:0')
            ->default(0)
            ->attribute(['id' => 'extra-coins-input'])
            ->help('<span id="total-coins-help" style="color: #007bff; font-weight: bold;"></span>');

        $this->text('reason_en', __('reason en'));
        $this->text('reason_ar', __('reason ar'));

        $this->select('form', __('add invoice'))
            ->options([
                0 => __('no'),
                1 => __('yes'),
            ])
            ->attribute(['id' => 'form-select']);

        $this->text('invoice', __('invoice'))
            ->attribute([
                'id' => 'invoice-field',
                'type' => 'file',
                'accept' => 'image/*',
                'style' => 'padding: 10px; background: transparent; border: 2px dashed #4a5568; border-radius: 12px; cursor: pointer; color: #a0aec0;',
            ]);

        $this->hidden('amount_type')->value(1);

        $transEquivalent = __('Equivalent:');
        $transCoins = __('Coins');
        $transUsd = __('Dollar');
        $transTotalSent = __('Total sent:');
        $transBase = __('Base');
        $transExtra = __('Extra');

        Admin::script("
            var transEquivalent = '{$transEquivalent}';
            var transCoins = '{$transCoins}';
            var transUsd = '{$transUsd}';
            var transTotalSent = '{$transTotalSent}';
            var transBase = '{$transBase}';
            var transExtra = '{$transExtra}';
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
                var extraCoinsVal = parseFloat($('#extra-coins-input').val()) || 0;
                var unit = $('#amount-unit-select').val();
                var cashbackType = $('#cashback-type-select').val();

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

                var extraCoins = 0;
                if (cashbackType === 'percent') {
                    extraCoins = baseCoins * (extraCoinsVal / 100);
                } else {
                    extraCoins = extraCoinsVal;
                }

                var totalCoins = baseCoins + extraCoins;
                $('#total-coins-help').text(transTotalSent + ' ' + totalCoins.toLocaleString() + ' ' + transCoins + ' (' + transBase + ': ' + baseCoins.toLocaleString() + ' + ' + transExtra + ': '+ extraCoins.toLocaleString() +')');
            }

            $(document).off('change', '#form-select').on('change', '#form-select', toggleInvoiceField);

            $(document).on('input', '#amount-input, #extra-coins-input', updateConversions);
            $(document).on('change', '#amount-unit-select, #cashback-type-select', updateConversions);


            $(document).on('shown.bs.modal', function() {
                setTimeout(toggleInvoiceField, 100);
            });

            toggleInvoiceField();
            updateConversions();
        SCRIPT);
    }

    public function html(): string
    {
        $title = __('dashboard.add_coins');
        $shippingReports = __('Charge reports');
        $url = url('admin/area-manager-charges-report/' . $this->userId);

        $html = '';

        if (Admin::user()->can('add-switch-charge-to-user') || Admin::user()->can('*')) {
            $html .= '<a href="javascript:void(0);" onclick="pu(' . $this->userId . ')" class="charge_action btn btn-sm btn-action">'
                . htmlspecialchars($title) .
                '</a>';
            $html .= '&nbsp;&nbsp;';
        }

        if (Admin::user()->can('history-switch-charge-to-user') || Admin::user()->can('*')) {
            $html .= '<a href="' . htmlspecialchars($url) . '"
            class="shipping_report btn btn-sm btn-action"
            onclick="initDatePickersAfterNav()">'
                . htmlspecialchars($shippingReports) .
                '</a>';
        }

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
