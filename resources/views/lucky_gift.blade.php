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
            @else
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
                                        value="{{ $settings['fair_luck_app_fee_rate'] ?? '0.10' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Receiver Fee Rate (0.10 = 10%)') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" step="0.01" name="fair_luck_receiver_fee_rate" class="form-control"
                                        value="{{ $settings['fair_luck_receiver_fee_rate'] ?? '0.10' }}">
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
                                    value="{{ (isset($config['lucky_gift_coins']) && $config['lucky_gift_coins'] != 0) ? $config['lucky_gift_coins'] : 2000 }}"
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