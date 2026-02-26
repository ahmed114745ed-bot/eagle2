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
                    <input type="text" name="fair_luck_app_fee_rate" class="form-control"
                        value="{{ $settings['fair_luck_app_fee_rate'] ?? '0.10' }}">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">{{ __('Receiver Fee Rate (0.10 = 10%)') }}</label>
                <div class="col-sm-8">
                    <input type="text" name="fair_luck_receiver_fee_rate" class="form-control"
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
    var ctx = document.getElementById('vaultChart').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($history->pluck('created_at')->map(fn($d) => $d->format('m-d H:i'))->toArray()) !!},
            datasets: [{
                label: 'Global Vault Balance',
                data: {!! json_encode($history->pluck('balance_after')->toArray()) !!},
                borderColor: 'rgba(60,141,188,0.8)',
                backgroundColor: 'rgba(60,141,188,0.2)',
                fill: true,
                tension: 0.1
            }]
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: false
                }
            }
        }
    });
</script>