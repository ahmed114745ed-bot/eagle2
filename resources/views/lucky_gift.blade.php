@php
    $luckyStatus = $config['lucky_gifts_action'] ?? 0;
    $currentVersion = $config['lucky_gift_version'] ?? 1;
@endphp

<style>
    .nav-tabs-custom>.nav-tabs>li.active {
        border-top-color: #3c8dbc;
    }

    /* RTL Support for Horizontal Form Labels */
    body.rtl .form-horizontal .control-label,
    [dir="rtl"] .form-horizontal .control-label {
        text-align: left !important;
    }

    body:not(.rtl):not([dir="rtl"]) .form-horizontal .control-label {
        text-align: right !important;
    }

    /* Ensure vertical alignment is consistent */
    .form-horizontal .control-label {
        padding-top: 7px;
        margin-bottom: 0;
    }
</style>

<div class="nav-tabs-custom">
    <ul class="nav nav-tabs">
        <li class="active"><a href="#tab_1" data-toggle="tab">{{ __('Luck gift Settings') }}</a></li>
        <li><a href="#tab_2" data-toggle="tab">{{ __('lucky gift coins') }}</a></li>
    </ul>
    <div class="tab-content">
        {{-- Tab 1: Luck gift Settings --}}
        <div class="tab-pane active" id="tab_1">

            @if($luckyStatus == 1)
                <div class="box box-solid box-default" style="border: 1px solid #eee; margin-bottom: 20px;">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-refresh"></i> {{ __('Choose Lucky Gift Version') }}</h3>
                    </div>
                    <div class="box-body">
                        <form action="{{ route('admin.lucky-gift.version.update') }}" method="POST" class="form-inline">
                            @csrf
                            <div class="radio-group" style="display: flex; gap: 20px; flex-wrap: wrap;">
                                <label
                                    style="margin: 0; padding: 10px 20px; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; display: flex; align-items: center; gap: 10px; {{ $currentVersion == 1 ? 'background: #f4f4f4; border-color: #3c8dbc;' : '' }}">
                                    <input type="radio" name="lucky_gift_version" value="1" {{ $currentVersion == 1 ? 'checked' : '' }} onchange="this.form.submit()" style="margin: 0;">
                                    <strong>{{ __('Version 1 (Standard)') }}</strong>
                                </label>
                                <label
                                    style="margin: 0; padding: 10px 20px; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; display: flex; align-items: center; gap: 10px; {{ $currentVersion == 2 ? 'background: #f4f4f4; border-color: #3c8dbc;' : '' }}">
                                    <input type="radio" name="lucky_gift_version" value="2" {{ $currentVersion == 2 ? 'checked' : '' }} onchange="this.form.submit()" style="margin: 0;">
                                    <strong>{{ __('Version 2 (FairLuck)') }}</strong>
                                </label>
                                <label
                                    style="margin: 0; padding: 10px 20px; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; display: flex; align-items: center; gap: 10px; {{ $currentVersion == 3 ? 'background: #f4f4f4; border-color: #3c8dbc;' : '' }}">
                                    <input type="radio" name="lucky_gift_version" value="3" {{ $currentVersion == 3 ? 'checked' : '' }} onchange="this.form.submit()" style="margin: 0;">
                                    <strong>{{ __('Version 3 (FairLuck V6)') }}</strong>
                                </label>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Version 1 Content --}}
            @if($currentVersion == 1 || $luckyStatus == 0)
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ __('Luck gift Settings (V1)') }}</h3>
                    </div>
                    <form action="{{ route('admin.lucky-gift.version.update') }}" method="POST" class="form-horizontal">
                        @csrf
                        <div class="box-body">
                            @if($errors->any())
                                <div class="alert alert-danger alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                    <h4><i class="icon fa fa-ban"></i> {{ __('Error!') }}</h4>
                                    <ul>
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('application wallet percentage') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" step="any" name="app_wallet_lucky_gift" class="form-control"
                                        value="{{ $config['app_wallet_lucky_gift'] ?? 0 }}" required>
                                    <span class="help-block">{{ __('App owner profit') }}</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('owner percentage') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" step="any" name="owner_lucky_gift" class="form-control"
                                        value="{{ $config['owner_lucky_gift'] ?? 0 }}" required>
                                    <span class="help-block">{{ __('owner gift') }}</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('host percentage') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" step="any" name="host_lucky_gift" class="form-control"
                                        value="{{ $config['host_lucky_gift'] ?? 0 }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-info pull-right">{{ __('Save V1 Settings') }}</button>
                        </div>
                    </form>
                </div>
            @elseif($currentVersion == 2)
                {{-- Version 2 Content (Matched with fairluck/dashboard.blade.php) --}}
                @php $settings = $fairLuckSettings; @endphp
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ __('FairLuck Settings') }}</h3>
                    </div>
                    <form action="{{ admin_url('fairluck/save-settings') }}" method="post" class="form-horizontal">
                        @csrf
                        <div class="box-body">
                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Global Vault Negative Limit') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" name="global_vault_negative_limit" class="form-control"
                                        value="{{ $settings['global_vault_negative_limit'] ?? 30000 }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('App Fee Rate (0.10 = 10%)') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" step="0.01" name="fair_luck_app_fee_rate" class="form-control"
                                        value="{{ $settings['fair_luck_app_fee_rate'] ?? '0' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Receiver Fee Rate (0.10 = 10%)') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" step="0.01" name="fair_luck_receiver_fee_rate" class="form-control"
                                        value="{{ $settings['fair_luck_receiver_fee_rate'] ?? '0' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Owner Fee Rate (0.10 = 10%)') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" step="0.01" name="fair_luck_owner_fee_rate" class="form-control"
                                        value="{{ $settings['fair_luck_owner_fee_rate'] ?? '0' }}">
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary pull-right">{{ __('Save') }}</button>
                        </div>
                    </form>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="box box-success">
                            <div class="box-header with-border">
                                <h3 class="box-title">{{ __('Global Vault Balance History') }}</h3>
                            </div>
                            <div class="box-body">
                                <canvas id="vaultChart" style="height: 300px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($currentVersion == 3)
                {{-- Version 3 Content (FairLuck V6 - Same settings as V2) --}}
                @php $settings = $fairLuckSettings; @endphp
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ __('FairLuck V6 Settings (Advanced Protection)') }} / {{ __('إعدادات FairLuck V6 (الحماية المتقدمة)') }}</h3>
                    </div>
                    <form action="{{ admin_url('fairluck/save-settings') }}" method="post" class="form-horizontal">
                        @csrf
                        <div class="box-body">
                            <div class="alert alert-info alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h4><i class="icon fa fa-info"></i> {{ __('FairLuck V6 Features') }} / {{ __('ميزات FairLuck V6') }}</h4>
                                <ul style="margin: 10px 0 0 20px;">
                                    <li>{{ __('✅ Bankruptcy Protection: Prevents system insolvency') }} / {{ __('✅ حماية الإفلاس: منع إعسار النظام') }}</li>
                                    <li>{{ __('✅ Pool Solvency: Safety margins for big jackpots') }} / {{ __('✅ ملاءة المجموعة: هوامش أمان للجوائز الكبرى') }}</li>
                                    <li>{{ __('✅ Post-Jackpot Cooldown: 200 bets between big wins') }} / {{ __('✅ فترة الانتظار بعد الجائزة الكبرى: 200 رهان بين الفوز الكبير') }}</li>
                                    <li>{{ __('✅ User Data TTL: Auto-reset after 90 days inactivity') }} / {{ __('✅ TTL بيانات المستخدم: إعادة تعيين تلقائية بعد 90 يوم من عدم النشاط') }}</li>
                                    <li>{{ __('✅ Reduced Jackpot Weights: 50% reduction for 1000x & 500x') }} / {{ __('✅ أوزان الجوائز المخفضة: تقليل 50% لـ 1000x و 500x') }}</li>
                                    <li>{{ __('✅ Improved RTP: 85% (vs 90% in V2)') }} / {{ __('✅ RTP محسّن: 85% (مقابل 90% في V2)') }}</li>
                                </ul>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Global Vault Negative Limit') }} / {{ __('حد الرصيد السالب العام') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" name="global_vault_negative_limit" class="form-control"
                                        value="{{ $settings['global_vault_negative_limit'] ?? 30000 }}">
                                    <span class="help-block">{{ __('Maximum allowed negative balance') }} / {{ __('الحد الأقصى للرصيد السالب المسموح به') }}</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Bankruptcy Min Safe Balance') }} / {{ __('الحد الأدنى الآمن لحماية الإفلاس') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" name="bankruptcy_min_safe_balance" class="form-control"
                                        value="{{ $settings['bankruptcy_min_safe_balance'] ?? 100000 }}">
                                    <span class="help-block">{{ __('Minimum safe balance threshold') }} / {{ __('حد الرصيد الآمن الأدنى') }}</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Bankruptcy Critical Threshold') }} / {{ __('حد الإنذار الحرج للإفلاس') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" name="bankruptcy_critical_threshold" class="form-control"
                                        value="{{ $settings['bankruptcy_critical_threshold'] ?? 50000 }}">
                                    <span class="help-block">{{ __('Critical alert threshold') }} / {{ __('حد الإنذار الحرج') }}</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Max Payout Percentage') }} / {{ __('أقصى نسبة دفع') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" step="0.01" name="bankruptcy_max_payout_percentage" class="form-control"
                                        value="{{ $settings['bankruptcy_max_payout_percentage'] ?? 0.15 }}">
                                    <span class="help-block">{{ __('Maximum payout as % of pool (0.15 = 15%)') }} / {{ __('أقصى دفع كنسبة من المجموعة (0.15 = 15%)') }}</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Max Probability Cap') }} / {{ __('الحد الأقصى لسقف الاحتمالية') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" step="0.01" name="v6_max_probability_cap" class="form-control"
                                        value="{{ $settings['v6_max_probability_cap'] ?? 0.50 }}">
                                    <span class="help-block">{{ __('Hard cap on win probability (0.50 = 50%)') }} / {{ __('حد صارم على احتمالية الفوز (0.50 = 50%)') }}</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Jackpot Cooldown Bets') }} / {{ __('رهانات فترة الانتظار للجائزة الكبرى') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" name="fairluck_jackpot_cooldown_bets" class="form-control"
                                        value="{{ $settings['fairluck_jackpot_cooldown_bets'] ?? 200 }}">
                                    <span class="help-block">{{ __('Required bets between big jackpots (250x+)') }} / {{ __('الرهانات المطلوبة بين الجوائز الكبرى (250x+)') }}</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('User Data TTL (Days)') }} / {{ __('TTL بيانات المستخدم (أيام)') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" name="fairluck_user_data_ttl_days" class="form-control"
                                        value="{{ $settings['fairluck_user_data_ttl_days'] ?? 90 }}">
                                    <span class="help-block">{{ __('Days before user RTP data expires') }} / {{ __('الأيام قبل انتهاء صلاحية بيانات RTP للمستخدم') }}</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Target RTP') }} / {{ __('RTP المستهدف') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" step="0.01" name="v6_target_rtp" class="form-control"
                                        value="{{ $settings['v6_target_rtp'] ?? 0.85 }}">
                                    <span class="help-block">{{ __('Target Return to Player (0.85 = 85%)') }} / {{ __('العائد المستهدف للاعب (0.85 = 85%)') }}</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('App Fee Rate (0.10 = 10%)') }} / {{ __('معدل رسوم التطبيق (0.10 = 10%)') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" step="0.01" name="fair_luck_app_fee_rate" class="form-control"
                                        value="{{ $settings['fair_luck_app_fee_rate'] ?? '0' }}">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Receiver Fee Rate (0.10 = 10%)') }} / {{ __('معدل رسوم المستقبل (0.10 = 10%)') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" step="0.01" name="fair_luck_receiver_fee_rate" class="form-control"
                                        value="{{ $settings['fair_luck_receiver_fee_rate'] ?? '0' }}">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Owner Fee Rate (0.10 = 10%)') }} / {{ __('معدل رسوم المالك (0.10 = 10%)') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" step="0.01" name="fair_luck_owner_fee_rate" class="form-control"
                                        value="{{ $settings['fair_luck_owner_fee_rate'] ?? '0' }}">
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-success pull-right">{{ __('Save V6 Settings') }} / {{ __('حفظ إعدادات V6') }}</button>
                        </div>
                    </form>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="box box-info">
                            <div class="box-header with-border">
                                <h3 class="box-title">{{ __('Global Vault Inventory Statistics') }} / {{ __('إحصائيات مخزون الخزينة العام') }}</h3>
                            </div>
                            <div class="box-body">
                                @php
                                    $vaultBalance = $fairLuckSettings['global_vault_balance'] ?? 0;
                                    $isPositive = $vaultBalance >= 0;
                                    $statusColor = $isPositive ? 'success' : 'danger';
                                    $statusText = $isPositive ? __('Positive') : __('Negative');
                                    $statusTextAr = $isPositive ? __('موجب') : __('سالب');
                                @endphp
                                <div class="alert alert-{{ $statusColor }}">
                                    <h4><i class="icon fa {{ $isPositive ? 'fa-check-circle' : 'fa-exclamation-circle' }}"></i> 
                                        {{ __('Current Vault Balance') }} / {{ __('رصيد الخزينة الحالي') }}
                                    </h4>
                                    <p style="font-size: 18px; font-weight: bold;">
                                        {{ number_format($vaultBalance, 2) }}
                                        <span style="margin-left: 20px; padding: 5px 15px; border-radius: 4px; background-color: rgba(0,0,0,0.1);">
                                            {{ $statusText }} / {{ $statusTextAr }}
                                        </span>
                                    </p>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-green"><i class="fa fa-arrow-up"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">{{ __('Above Zero') }} / {{ __('فوق الصفر') }}</span>
                                                <span class="info-box-number">{{ $isPositive ? '✓ ' . __('Yes') . ' / نعم' : '✗ ' . __('No') . ' / لا' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-red"><i class="fa fa-arrow-down"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">{{ __('Below Zero') }} / {{ __('تحت الصفر') }}</span>
                                                <span class="info-box-number">{{ !$isPositive ? '✓ ' . __('Yes') . ' / نعم' : '✗ ' . __('No') . ' / لا' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row" style="margin-top: 20px;">
                                    <div class="col-md-12">
                                        <div class="box box-default">
                                            <div class="box-header with-border">
                                                <h3 class="box-title">{{ __('Safety Thresholds') }} / {{ __('حدود الأمان') }}</h3>
                                            </div>
                                            <div class="box-body">
                                                <table class="table table-striped">
                                                    <tr>
                                                        <td><strong>{{ __('Min Safe Balance') }} / {{ __('الحد الأدنى الآمن') }}</strong></td>
                                                        <td>{{ number_format($settings['bankruptcy_min_safe_balance'] ?? 100000, 0) }}</td>
                                                        <td>
                                                            @if($vaultBalance >= ($settings['bankruptcy_min_safe_balance'] ?? 100000))
                                                                <span class="label label-success">{{ __('Safe') }} / {{ __('آمن') }}</span>
                                                            @else
                                                                <span class="label label-warning">{{ __('Warning') }} / {{ __('تحذير') }}</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>{{ __('Critical Threshold') }} / {{ __('حد الإنذار الحرج') }}</strong></td>
                                                        <td>{{ number_format($settings['bankruptcy_critical_threshold'] ?? 50000, 0) }}</td>
                                                        <td>
                                                            @if($vaultBalance >= ($settings['bankruptcy_critical_threshold'] ?? 50000))
                                                                <span class="label label-success">{{ __('Safe') }} / {{ __('آمن') }}</span>
                                                            @else
                                                                <span class="label label-danger">{{ __('Critical') }} / {{ __('حرج') }}</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>{{ __('Negative Limit') }} / {{ __('حد الرصيد السالب') }}</strong></td>
                                                        <td>-{{ number_format($settings['global_vault_negative_limit'] ?? 30000, 0) }}</td>
                                                        <td>
                                                            @if($vaultBalance >= -($settings['global_vault_negative_limit'] ?? 30000))
                                                                <span class="label label-success">{{ __('Within Limit') }} / {{ __('ضمن الحد') }}</span>
                                                            @else
                                                                <span class="label label-danger">{{ __('Exceeded') }} / {{ __('تجاوز') }}</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Tab 2: lucky gift coins --}}
        <div class="tab-pane" id="tab_2">
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ __('lucky gift coins') }}</h3>
                </div>
                <form action="{{ route('admin.lucky-gift.version.update') }}" method="POST" class="form-horizontal">
                    @csrf
                    <div class="box-body">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{ __('lucky gift coins') }}</label>
                            <div class="col-sm-8">
                                <input type="number" name="lucky_gift_coins" class="form-control"
                                         value="{{ $config['lucky_gift_coins'] ?? 0 }}"
                                    required>
                                <span
                                    class="help-block">{{ __('Play coin sound inside the room when the win amount is greater than or equal to the added value.') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-warning pull-right">{{ __('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@if($luckyStatus == 1 && $currentVersion == 2 && isset($history))
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(function () {
            var historyData = {!! json_encode($history->map(function ($h) {
            return [
                'date' => $h->created_at ? $h->created_at->format('m-d H:i:s') : '',
                'before' => (int) $h->balance_before,
                'change' => (int) $h->amount,
                'after' => (int) $h->balance_after,
                'desc' => $h->description ?? __('Transaction')
            ];
        })->toArray()) !!};

            var labels = historyData.map(function (d) { return d.date; });
            var dataPoints = historyData.map(function (d) { return d.after; });

            var ctx = document.getElementById('vaultChart').getContext('2d');
            var chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: "{{ __('Global Vault Balance') }}",
                        data: dataPoints,
                        borderColor: 'rgba(60,141,188,0.8)',
                        backgroundColor: 'rgba(60,141,188,0.2)',
                        fill: true,
                        tension: 0.1,
                        pointBackgroundColor: function (context) {
                            var val = dataPoints[context.dataIndex] || 0;
                            return val < 0 ? 'rgba(255,99,132,1)' : 'rgba(60,141,188,1)';
                        },
                        pointBorderColor: '#fff',
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'nearest',
                        intersect: false,
                    },
                    plugins: {
                        tooltip: {
                            padding: 10,
                            callbacks: {
                                label: function (context) {
                                    var data = historyData[context.dataIndex];
                                    var lines = [];
                                    lines.push("{{ __('🎯 Balance After:  ') }}" + data.after.toLocaleString());
                                    lines.push("{{ __('💰 Change Amt:  ') }}" + (data.change > 0 ? '+' : '') + data.change.toLocaleString());
                                    lines.push("{{ __('⏳ Balance Before: ') }}" + data.before.toLocaleString());

                                    var desc = data.desc;
                                    desc = desc.replace('Win payout', "{{ __('🏆 Win') }}")
                                        .replace('Loss bet', "{{ __('💔 Loss') }}")
                                        .replace('Bet contribution', "{{ __('💸 Bet') }}");

                                    lines.push("{{ __('📝 Info: ') }}" + desc);

                                    if (data.after < 0) {
                                        lines.push("{{ __('⚠️ Status: Wallet is Negative!') }}");
                                    }
                                    return lines;
                                }
                            }
                        }
                    },
                    scales: {
                        y: { beginAtZero: false }
                    }
                }
            });
        });
    </script>
@endif