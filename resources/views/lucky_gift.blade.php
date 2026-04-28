@php
    $luckyStatus = $config['lucky_gifts_action'] ?? 1;
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
                                    style="margin: 0; padding: 10px 20px; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; display: flex; align-items: center; gap: 10px; {{ $currentVersion == 4 ? 'background: #f4f4f4; border-color: #3c8dbc;' : '' }}">
                                    <input type="radio" name="lucky_gift_version" value="4" {{ $currentVersion == 4 ? 'checked' : '' }} onchange="this.form.submit()" style="margin: 0;">
                                    <strong>{{ __('Version 2 (FairLuck V2)') }}</strong>
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
                {{-- Version 3 Content (FairLuck V6 - Same settings as V2) --}}
                @php $settings = $fairLuckSettings; @endphp
                {{-- <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ __('FairLuck V6 Settings (Advanced Protection)') }} / {{ __('إعدادات FairLuck V6 (الحماية المتقدمة)') }}</h3>
                    </div>
                    <form action="{{ admin_url('fairluck/save-settings') }}" method="post" class="form-horizontal">
                        @csrf
                        <div class="box-body">
                          

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

                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-success pull-right">{{ __('Save V6 Settings') }} / {{ __('حفظ إعدادات V6') }}</button>
                        </div>
                    </form>
                </div> --}}

                {{-- <div class="row">
                    <div class="col-md-12">
                        <div class="box box-success">
                            <div class="box-header with-border">
                                <h3 class="box-title">{{ __('Global Vault Balance History') }} / {{ __('سجل رصيد الخزينة العام') }}</h3>
                            </div>
                            <div class="box-body">
                                <canvas id="vaultChartV6" style="height: 300px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div> --}}
            @elseif($currentVersion == 4)
                {{-- Version 4 Content (FairLuck V7 - Simplified) --}}
                @php $settings = $fairLuckSettings; @endphp
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-sliders"></i> {{ __('FairLuck V2 Quick Settings') }}</h3>
                        <div class="box-tools">
                            <a href="{{ admin_url('fairluck') }}" class="btn btn-sm btn-default" title="{{ __('Advanced Settings') }}">
                                <i class="fa fa-cogs"></i> {{ __('Advanced Settings') }}
                            </a>
                        </div>
                    </div>
                    <form action="{{ admin_url('fairluck/save-settings') }}" method="post" class="form-horizontal">
                        @csrf
                        <div class="box-body">
                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('App Profit') }} / {{ __('نسبة ربح التطبيق') }}</label>
                                <div class="col-sm-4">
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="0" max="50" name="fair_luck_app_fee_rate" class="form-control"
                                            value="{{ isset($settings['fair_luck_app_fee_rate']) ? round((float)$settings['fair_luck_app_fee_rate'] * 100, 2) : 1.5 }}">
                                        <span class="input-group-addon">%</span>
                                    </div>
                                    <span class="help-block">{{ __('Recommended: 1.5%') }}</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Receiver Share') }} / {{ __('نسبة المستقبل') }}</label>
                                <div class="col-sm-4">
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="0" max="50" name="fair_luck_receiver_fee_rate" class="form-control"
                                            value="{{ isset($settings['fair_luck_receiver_fee_rate']) ? round((float)$settings['fair_luck_receiver_fee_rate'] * 100, 2) : 10 }}">
                                        <span class="input-group-addon">%</span>
                                    </div>
                                    <span class="help-block">{{ __('Recommended: 10%') }}</span>
                                </div>
                            </div>

     

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Target RTP') }} / {{ __('نسبة RTP') }}</label>
                                <div class="col-sm-4">
                                    <div class="input-group">
                                        <input type="number" step="0.1" min="70" max="100" name="V7_target_rtp" class="form-control"
                                            value="{{ isset($settings['V7_target_rtp']) ? round((float)$settings['V7_target_rtp'] * 100, 1) : 99 }}">
                                        <span class="input-group-addon">%</span>
                                    </div>
                                    <span class="help-block">{{ __('Recommended: 99%') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="box-footer">
                            <a href="{{ admin_url('fairluck') }}" class="btn btn-default"><i class="fa fa-cogs"></i> {{ __('All Settings') }}</a>
                            <button type="submit" class="btn btn-success pull-right"><i class="fa fa-save"></i> {{ __('Save') }}</button>
                        </div>
                    </form>
                </div>

                {{-- Vault Status Card --}}
                @php
                    $vaultBalance = \App\Models\FairLuckWallet::where('wallet_type', 'global_vault')->value('balance') ?? 0;
                    $walletMin = (int)($settings['V7_wallet_min'] ?? 10000);
                    $walletTight = (int)($settings['V7_wallet_tight'] ?? 50000);
                    $walletTarget = (int)($settings['V7_wallet_target'] ?? 200000);
                    $walletHigh = (int)($settings['V7_wallet_high'] ?? 500000);
                    $walletDrain = (int)($settings['V7_wallet_drain'] ?? 1000000);
                    $zone = $vaultBalance <= $walletMin ? 'CRITICAL' : ($vaultBalance <= $walletTight ? 'TIGHT' : ($vaultBalance <= $walletTarget ? 'NORMAL' : ($vaultBalance <= $walletHigh ? 'GENEROUS' : 'DRAIN')));
                    $zoneColor = ['CRITICAL' => '#dc3545', 'TIGHT' => '#e67e22', 'NORMAL' => '#3498db', 'GENEROUS' => '#28a745', 'DRAIN' => '#8e44ad'][$zone];
                    $appWallet = \App\Models\CoreWallet::where('name', 'app_wallet')->value('coins') ?? 0;
                @endphp
                <div class="row">
                    <div class="col-md-6">
                        <div class="small-box" style="background: {{ $zoneColor }}; color: #fff;">
                            <div class="inner">
                                <h3>{{ number_format($vaultBalance) }}</h3>
                                <p>{{ __('Lucky Wallet') }} — {{ $zone }}</p>
                            </div>
                            <div class="icon"><i class="fa fa-diamond"></i></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="small-box bg-orange" style="color: #fff;">
                            <div class="inner">
                                <h3>{{ number_format($appWallet) }}</h3>
                                <p>{{ __('App Wallet') }}</p>
                            </div>
                            <div class="icon"><i class="fa fa-money"></i></div>
                        </div>
                    </div>
                </div>

                {{-- Live Vault Chart --}}
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-line-chart"></i> {{ __('Vault Balance — Live') }}</h3>
                        <div class="box-tools">
                            <div class="btn-group btn-group-sm" id="chartRange">
                                <button class="btn btn-default" data-range="50">{{ __('Last 50') }}</button>
                                <button class="btn btn-default active" data-range="100">{{ __('Last 100') }}</button>
                                <button class="btn btn-default" data-range="500">{{ __('Last 500') }}</button>
                                <button class="btn btn-default" data-range="all">{{ __('All') }}</button>
                            </div>
                            <span id="autoRefreshLabel" class="label label-success" style="margin-left:10px;">{{ __('Auto-refresh: 30s') }}</span>
                        </div>
                    </div>
                    <div class="box-body">
                        <canvas id="vaultChartV7" style="height: 350px;"></canvas>
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

@if($luckyStatus == 1 && isset($history))
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(function () {
            // Live Preview Bar Chart for Multiplier Weights (V7 only)
            @if($currentVersion == 4)
            var weightChart = null;
            function updateWeightChart() {
                var labels = [];
                var data = [];
                var totalWeight = 0;
                var colors = [
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(255, 159, 64, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(231, 76, 60, 0.8)',
                    'rgba(142, 68, 173, 0.8)',
                    'rgba(39, 174, 96, 0.8)'
                ];

                $('.weight-input').each(function() {
                    var val = parseInt($(this).val()) || 0;
                    totalWeight += val;
                });

                $('.weight-input').each(function() {
                    var mult = $(this).data('multiplier');
                    var val = parseInt($(this).val()) || 0;
                    var pct = totalWeight > 0 ? ((val / totalWeight) * 100).toFixed(1) : 0;
                    labels.push(mult + 'x (' + pct + '%)');
                    data.push(val);
                });

                var canvas = document.getElementById('weightPreviewChart');
                if (!canvas) return;

                if (weightChart) {
                    weightChart.destroy();
                }

                weightChart = new Chart(canvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Weight',
                            data: data,
                            backgroundColor: colors,
                            borderColor: colors.map(function(c) { return c.replace('0.8', '1'); }),
                            borderWidth: 1
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        var total = context.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                                        var pct = total > 0 ? ((context.raw / total) * 100).toFixed(1) : 0;
                                        return 'Weight: ' + context.raw + ' (' + pct + '%)';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: { beginAtZero: true, title: { display: true, text: 'Weight' } }
                        }
                    }
                });
            }

            // Initialize chart and update on input change
            updateWeightChart();
            $(document).on('input change', '.weight-input', function() {
                updateWeightChart();
            });
            @endif

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

            var chartId = '{{ $currentVersion == 3 ? "vaultChartV6" : ($currentVersion == 4 ? "vaultChartV7" : "vaultChart") }}';
            var canvas = document.getElementById(chartId);
            if (!canvas) return;

            @if($currentVersion == 4)
            // V7: Enhanced chart with zone lines, time ranges, auto-refresh
            var ZONES = {
                min: {{ $settings['V7_wallet_min'] ?? 10000 }},
                tight: {{ $settings['V7_wallet_tight'] ?? 50000 }},
                target: {{ $settings['V7_wallet_target'] ?? 200000 }},
                high: {{ $settings['V7_wallet_high'] ?? 500000 }},
                drain: {{ $settings['V7_wallet_drain'] ?? 1000000 }}
            };
            var allData = historyData;
            var currentRange = 100;
            var vaultChart = null;

            function getZone(val) {
                if (val <= ZONES.min) return 'CRITICAL';
                if (val <= ZONES.tight) return 'TIGHT';
                if (val <= ZONES.target) return 'NORMAL';
                if (val <= ZONES.high) return 'GENEROUS';
                return 'DRAIN';
            }

            function getZoneColor(val) {
                var z = getZone(val);
                return {CRITICAL:'#dc3545',TIGHT:'#e67e22',NORMAL:'#3498db',GENEROUS:'#28a745',DRAIN:'#8e44ad'}[z];
            }

            function renderChart(range) {
                currentRange = range;
                var data = range === 'all' ? allData : allData.slice(-range);
                var labs = data.map(function(d) { return d.date; });
                var pts = data.map(function(d) { return d.after; });

                if (vaultChart) vaultChart.destroy();

                // Zone threshold datasets
                var zoneLines = [
                    { label: 'CRITICAL (' + ZONES.min.toLocaleString() + ')', data: Array(pts.length).fill(ZONES.min), borderColor: '#dc3545', borderDash: [5,5], borderWidth: 1, pointRadius: 0, fill: false },
                    { label: 'TIGHT (' + ZONES.tight.toLocaleString() + ')', data: Array(pts.length).fill(ZONES.tight), borderColor: '#e67e22', borderDash: [5,5], borderWidth: 1, pointRadius: 0, fill: false },
                    { label: 'TARGET (' + ZONES.target.toLocaleString() + ')', data: Array(pts.length).fill(ZONES.target), borderColor: '#3498db', borderDash: [8,4], borderWidth: 2, pointRadius: 0, fill: false },
                    { label: 'GENEROUS (' + ZONES.high.toLocaleString() + ')', data: Array(pts.length).fill(ZONES.high), borderColor: '#28a745', borderDash: [5,5], borderWidth: 1, pointRadius: 0, fill: false },
                ];

                vaultChart = new Chart(canvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: labs,
                        datasets: [{
                            label: '{{ __("Vault Balance") }}',
                            data: pts,
                            borderColor: 'rgba(60,141,188,1)',
                            backgroundColor: 'rgba(60,141,188,0.15)',
                            fill: true, tension: 0.2, borderWidth: 2,
                            pointBackgroundColor: function(ctx) { return getZoneColor(pts[ctx.dataIndex] || 0); },
                            pointRadius: pts.length > 200 ? 0 : 3,
                            pointHoverRadius: 6
                        }].concat(zoneLines)
                    },
                    options: {
                        maintainAspectRatio: false,
                        interaction: { mode: 'nearest', intersect: false },
                        plugins: {
                            legend: { position: 'bottom', labels: { usePointStyle: true, font: { size: 11 } } },
                            tooltip: {
                                padding: 12, titleFont: { size: 13 }, bodyFont: { size: 12 },
                                callbacks: {
                                    title: function(items) {
                                        var d = data[items[0].dataIndex];
                                        return d ? d.date : '';
                                    },
                                    label: function(ctx) {
                                        if (ctx.datasetIndex > 0) return null; // skip zone lines
                                        var d = data[ctx.dataIndex];
                                        if (!d) return '';
                                        var zone = getZone(d.after);
                                        return [
                                            'Balance: ' + d.after.toLocaleString() + ' [' + zone + ']',
                                            'Change: ' + (d.change >= 0 ? '+' : '') + d.change.toLocaleString(),
                                            'Before: ' + d.before.toLocaleString(),
                                            d.desc.replace('V7 bet credit','Bet').replace('V7 win payout','Win Payout')
                                        ];
                                    }
                                }
                            }
                        },
                        scales: {
                            x: { ticks: { maxTicksLimit: 15, font: { size: 10 } } },
                            y: {
                                beginAtZero: false,
                                ticks: { callback: function(v) { return v >= 1000 ? (v/1000).toFixed(0) + 'k' : v; } },
                                title: { display: true, text: '{{ __("Coins") }}' }
                            }
                        }
                    }
                });
            }

            renderChart(100);

            // Time range buttons
            $('#chartRange button').on('click', function() {
                $('#chartRange button').removeClass('active');
                $(this).addClass('active');
                var r = $(this).data('range');
                renderChart(r === 'all' ? 'all' : parseInt(r));
            });

            // Auto-refresh every 30s
            setInterval(function() {
                $.getJSON('{{ admin_url("fairluck") }}?ajax=history', function(resp) {
                    if (resp && resp.length) {
                        allData = resp;
                        renderChart(currentRange);
                    }
                }).fail(function() {}); // silent fail
            }, 30000);

            @else
            // V2/V3: Simple chart
            var chart = new Chart(canvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: "{{ __('Vault Balance') }}",
                        data: dataPoints,
                        borderColor: 'rgba(60,141,188,0.8)',
                        backgroundColor: 'rgba(60,141,188,0.2)',
                        fill: true, tension: 0.1,
                        pointBackgroundColor: function(ctx) { return (dataPoints[ctx.dataIndex]||0) < 0 ? '#e74c3c' : '#3c8dbc'; },
                        pointRadius: 4, pointHoverRadius: 6
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    var d = historyData[ctx.dataIndex];
                                    return ['Balance: ' + d.after.toLocaleString(), 'Change: ' + (d.change>0?'+':'') + d.change.toLocaleString(), d.desc];
                                }
                            }
                        }
                    },
                    scales: { y: { beginAtZero: false } }
                }
            });
            @endif
        });
    </script>
@endif
