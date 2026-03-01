<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">{{ __('FairLuck Settings') }}</h3>
    </div>
    <form action="{{ admin_url('fairluck/save-settings') }}" method="post" class="form-horizontal">
        @csrf
        <div class="box-body">
            <div class="form-group">
                <label class="col-sm-2 control-label">{{ __('Global Vault Negative Limit') }}</label>
                <div class="col-sm-8">
                    <input type="number" name="global_vault_negative_limit" class="form-control"
                        value="{{ $settings['global_vault_negative_limit'] ?? 30000 }}">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">{{ __('App Fee Rate (0.10 = 10%)') }}</label>
                <div class="col-sm-8">
                    <input type="number" name="fair_luck_app_fee_rate" class="form-control"
                        value="{{ $settings['fair_luck_app_fee_rate'] ?? '0.10' }}">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">{{ __('Receiver Fee Rate (0.10 = 10%)') }}</label>
                <div class="col-sm-8">
                    <input type="number" name="fair_luck_receiver_fee_rate" class="form-control"
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


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    var historyData = {!! json_encode($history->map(function ($h) {
    return [
        'date' => $h->created_at ? $h->created_at->format('m-d H:i:s') : '',
        'before' => (int) $h->balance_before,
        'change' => (int) $h->amount,
        'after' => (int) $h->balance_after,
        'desc' => $h->description ?? 'Transaction'
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
                label: 'Global Vault Balance',
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
                            lines.push('🎯 Balance After:  ' + data.after.toLocaleString());
                            lines.push('💰 Change Amt:  ' + (data.change > 0 ? '+' : '') + data.change.toLocaleString());
                            lines.push('⏳ Balance Before: ' + data.before.toLocaleString());

                            // Wrap long description across lines if needed, or truncate (assuming descriptions are usually 1 line here)
                            var desc = data.desc.replace('Win payout', '🏆 Win').replace('Loss bet', '💔 Loss').replace('Bet contribution', '💸 Bet');
                            lines.push('📝 Info: ' + desc);

                            if (data.after < 0) {
                                lines.push('⚠️ Status: Wallet is Negative!');
                            }
                            return lines;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: false
                }
            }
        }
    });
</script>