<style>
    .field-description { font-size: 12px; color: #666; margin-top: 5px; padding: 8px; background-color: #f9f9f9; border-left: 3px solid #3498db; border-radius: 3px; }
    .field-tooltip { cursor: help; color: #3498db; font-weight: bold; margin-left: 5px; }
    .section-header { margin-top: 20px; margin-bottom: 15px; color: #333; }
    .section-note { color: #666; font-size: 13px; margin-bottom: 15px; padding: 10px; background: #f0f8ff; border-left: 3px solid #2196F3; }
    .legacy-note { background: #fff3cd; border-left: 3px solid #ffc107; }
</style>

<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">{{ __('FairLuck V7 Settings — Single-Step Engine') }}</h3>
    </div>
    <form action="{{ admin_url('fairluck/save-settings') }}" method="post" class="form-horizontal">
        @csrf
        <div class="box-body">

            {{-- ===== SECTION 1: Fee Rates ===== --}}
            <h4 class="section-header">{{ __('Fee Rates') }}</h4>
            <p class="section-note">
                Money flow: User bets B coins. App takes appFee%. Remaining enters lucky wallet.
                On win: 10% to receiver, 10% to host, 80% to sender.
            </p>

            @php
                $pctFields = [
                    ['name' => 'fair_luck_app_fee_rate', 'label' => 'App Fee Rate', 'desc' => 'App fee deducted from every bet before entering the game. Default: 1.5%', 'default' => 0.015, 'min' => 0, 'max' => 20],
                    ['name' => 'fair_luck_receiver_fee_rate', 'label' => 'Receiver Payout Rate', 'desc' => 'Percentage of total payout sent to the gift receiver.', 'default' => 0.10, 'min' => 0, 'max' => 50],
                    ['name' => 'fair_luck_owner_fee_rate', 'label' => 'Host Payout Rate', 'desc' => 'Percentage of total payout sent to the room host/owner.', 'default' => 0.10, 'min' => 0, 'max' => 50],
                ];
            @endphp
            @foreach($pctFields as $f)
                <div class="form-group">
                    <label class="col-sm-2 control-label">{{ __($f['label']) }} <span class="field-tooltip" title="{{ $f['desc'] }}">i</span></label>
                    <div class="col-sm-8">
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input type="number" name="{{ $f['name'] }}" class="form-control" style="flex: 1;"
                                value="{{ (float)($settings[$f['name']] ?? $f['default']) * 100 }}"
                                step="0.1" min="{{ $f['min'] }}" max="{{ $f['max'] }}">
                            <span style="font-weight: bold; color: #666;">%</span>
                        </div>
                        <div class="field-description">{{ $f['desc'] }}</div>
                    </div>
                </div>
            @endforeach

            <hr>

            {{-- ===== SECTION 2: Target RTP & Loss Streak ===== --}}
            <h4 class="section-header">{{ __('RTP & Player Protection') }}</h4>

            <div class="form-group">
                <label class="col-sm-2 control-label">{{ __('Target RTP') }} <span class="field-tooltip" title="Target Return to Player percentage">i</span></label>
                <div class="col-sm-8">
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="number" name="V7_target_rtp" class="form-control" style="flex: 1;"
                            value="{{ isset($settings['V7_target_rtp']) ? (float)$settings['V7_target_rtp'] * 100 : 99 }}"
                            step="0.1" min="70" max="100">
                        <span style="font-weight: bold; color: #666;">%</span>
                    </div>
                    <div class="field-description">Target RTP for per-user correction. Sender RTP = (1 - appFee) x senderShare x E[M]. Default: 99%.</div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">{{ __('RTP Activation Threshold') }} <span class="field-tooltip" title="Min total bet before RTP correction activates">i</span></label>
                <div class="col-sm-8">
                    <input type="number" name="V7_rtp_activation" class="form-control"
                        value="{{ $settings['V7_rtp_activation'] ?? 500 }}" min="0" max="10000">
                    <div class="field-description">Minimum total coins bet before per-user RTP correction kicks in. Default: 500.</div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">{{ __('Max Loss Streak') }} <span class="field-tooltip" title="Force a win after this many consecutive losses">i</span></label>
                <div class="col-sm-8">
                    <input type="number" name="V7_max_loss_streak" class="form-control"
                        value="{{ $settings['V7_max_loss_streak'] ?? 20 }}" min="0" max="100">
                    <div class="field-description">Force a small win after this many consecutive losses. 0 = disabled. Default: 20.</div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">{{ __('Forced Win Multiplier') }} <span class="field-tooltip" title="Multiplier used when loss streak protection triggers">i</span></label>
                <div class="col-sm-8">
                    <input type="number" name="V7_forced_win_mult" class="form-control"
                        value="{{ $settings['V7_forced_win_mult'] ?? 5 }}" min="5" max="50">
                    <div class="field-description">Multiplier given when loss streak protection fires. Default: 5x.</div>
                </div>
            </div>

            <hr>

            {{-- ===== SECTION 3: Wallet Zone Thresholds ===== --}}
            <h4 class="section-header">{{ __('Wallet Zone Thresholds (Coins)') }}</h4>
            <p class="section-note">
                The engine adjusts win probability based on vault balance. Zones:
                CRITICAL (suppresses wins hard) -> TIGHT -> NORMAL (target equilibrium) -> GENEROUS -> DRAIN (boosts wins to drain excess).
            </p>

            @php
                $thresholds = [
                    ['name' => 'V7_wallet_min', 'label' => 'CRITICAL Floor', 'desc' => 'Below this = CRITICAL zone. Maximum win suppression. Jackpot gate blocks any payout that would breach this.', 'default' => 10000],
                    ['name' => 'V7_wallet_tight', 'label' => 'TIGHT Threshold', 'desc' => 'Below this = TIGHT zone. Wins suppressed, vault recovers.', 'default' => 50000],
                    ['name' => 'V7_wallet_target', 'label' => 'NORMAL Target', 'desc' => 'Equilibrium point. Engine aims to keep vault here.', 'default' => 200000],
                    ['name' => 'V7_wallet_high', 'label' => 'GENEROUS Threshold', 'desc' => 'Above this = GENEROUS zone. Wins boosted, big multipliers available.', 'default' => 500000],
                    ['name' => 'V7_wallet_drain', 'label' => 'DRAIN Threshold', 'desc' => 'Above this = DRAIN zone. Maximum win boosting to drain excess.', 'default' => 1000000],
                ];
            @endphp
            @foreach($thresholds as $t)
                <div class="form-group">
                    <label class="col-sm-2 control-label">{{ __($t['label']) }} <span class="field-tooltip" title="{{ $t['desc'] }}">i</span></label>
                    <div class="col-sm-8">
                        <input type="number" name="{{ $t['name'] }}" class="form-control"
                            value="{{ $settings[$t['name']] ?? $t['default'] }}" min="0">
                        <div class="field-description">{{ $t['desc'] }} Default: {{ number_format($t['default']) }}.</div>
                    </div>
                </div>
            @endforeach

            <div class="form-group">
                <label class="col-sm-2 control-label">{{ __('Negative Vault Limit') }} <span class="field-tooltip" title="How far negative the vault can go">i</span></label>
                <div class="col-sm-8">
                    <input type="number" name="V7_negative_limit" class="form-control"
                        value="{{ $settings['V7_negative_limit'] ?? 30000 }}" min="0">
                    <div class="field-description">Max negative balance allowed for the vault. Vault recovers via incoming bets when negative. Default: 30,000.</div>
                </div>
            </div>

            <hr>

            {{-- ===== SECTION 4: Weight Adjustment Sensitivity ===== --}}
            <h4 class="section-header">{{ __('Weight Adjustment Sensitivity') }}</h4>
            <p class="section-note">
                Controls how aggressively the engine self-corrects. Higher values = faster correction but more volatile.
                Combined factor = (walletFactor x walletWeight) + (rtpFactor x rtpWeight).
            </p>

            @php
                $sensitivity = [
                    ['name' => 'V7_nowin_sensitivity', 'label' => 'No-Win Sensitivity', 'desc' => 'How much the 0x (no-win) weight adjusts with combined factor. Default: 0.15.', 'default' => 0.15, 'step' => 0.01, 'min' => 0, 'max' => 1],
                    ['name' => 'V7_win_base_sensitivity', 'label' => 'Win Suppression Base', 'desc' => 'Base suppression factor when vault is low. Default: 0.7.', 'default' => 0.7, 'step' => 0.1, 'min' => 0, 'max' => 2],
                    ['name' => 'V7_win_position_sensitivity', 'label' => 'Win Suppression Position', 'desc' => 'Extra suppression for high-tier multipliers. Default: 2.0.', 'default' => 2.0, 'step' => 0.1, 'min' => 0, 'max' => 5],
                    ['name' => 'V7_boost_base_sensitivity', 'label' => 'Win Boost Base', 'desc' => 'Base boost factor when vault is generous. Default: 0.5.', 'default' => 0.5, 'step' => 0.1, 'min' => 0, 'max' => 2],
                    ['name' => 'V7_boost_position_sensitivity', 'label' => 'Win Boost Position', 'desc' => 'Extra boost for high-tier multipliers when generous. Default: 1.8.', 'default' => 1.8, 'step' => 0.1, 'min' => 0, 'max' => 5],
                    ['name' => 'V7_wallet_weight', 'label' => 'Wallet Factor Weight', 'desc' => 'Weight of wallet health in combined factor. Default: 0.70.', 'default' => 0.70, 'step' => 0.05, 'min' => 0, 'max' => 1],
                    ['name' => 'V7_rtp_weight', 'label' => 'RTP Factor Weight', 'desc' => 'Weight of user RTP correction in combined factor. Default: 0.30.', 'default' => 0.30, 'step' => 0.05, 'min' => 0, 'max' => 1],
                    ['name' => 'V7_nowin_floor', 'label' => 'No-Win Floor', 'desc' => 'Minimum weight for 0x tier (scaled). Ensures randomness always exists. Default: 50000.', 'default' => 50000, 'step' => 1000, 'min' => 10000, 'max' => 90000],
                ];
            @endphp
            @foreach($sensitivity as $s)
                <div class="form-group">
                    <label class="col-sm-2 control-label">{{ __($s['label']) }} <span class="field-tooltip" title="{{ $s['desc'] }}">i</span></label>
                    <div class="col-sm-8">
                        <input type="number" name="{{ $s['name'] }}" class="form-control"
                            value="{{ $settings[$s['name']] ?? $s['default'] }}"
                            step="{{ $s['step'] }}" min="{{ $s['min'] }}" max="{{ $s['max'] }}">
                        <div class="field-description">{{ $s['desc'] }}</div>
                    </div>
                </div>
            @endforeach

            <hr>

            {{-- ===== SECTION 5: Base Weights (JSON) ===== --}}
            <h4 class="section-header">{{ __('Multiplier Base Weights') }}</h4>
            <p class="section-note">
                Base probability distribution for all tiers. Sum should be ~100,000.
                E[M] = sum(mult x weight) / sum(weights). Higher 0x weight = fewer wins. Higher win tier weights = more wins.
            </p>

            <div class="form-group">
                <label class="col-sm-2 control-label">{{ __('Base Weights (JSON)') }} <span class="field-tooltip" title="JSON object: {multiplier: weight}">i</span></label>
                <div class="col-sm-8">
                    <textarea name="V7_base_weights" class="form-control" rows="12" style="font-family: monospace; font-size: 13px;">{{ $settings['V7_base_weights'] ?? json_encode(['0' => 93445, '5' => 4000, '10' => 1500, '20' => 600, '50' => 220, '100' => 110, '250' => 65, '500' => 38, '1000' => 22], JSON_PRETTY_PRINT) }}</textarea>
                    <div class="field-description">
                        JSON object mapping multiplier to base weight. Keys: 0 (no-win), 5, 10, 20, 50, 100, 250, 500, 1000.
                        These weights are adjusted dynamically by the wallet factor and RTP factor.
                    </div>
                </div>
            </div>

            <hr>

            {{-- ===== SECTION 6: Other ===== --}}
            <h4 class="section-header">{{ __('Other Settings') }}</h4>

            <div class="form-group">
                <label class="col-sm-2 control-label">{{ __('Coin to USD Rate') }}</label>
                <div class="col-sm-8">
                    <input type="number" name="coin_to_usd_rate" class="form-control"
                        value="{{ $settings['coin_to_usd_rate'] ?? 0.01 }}"
                        step="0.0001" min="0.0001" max="1.0000">
                    <div class="field-description">Conversion rate for display purposes.</div>
                </div>
            </div>

        </div>
        <div class="box-footer">
            <button type="submit" class="btn btn-primary pull-right">{{ __('Save') }}</button>
        </div>
    </form>
</div>

{{-- Vault Balance Chart --}}
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
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Global Vault Balance',
                data: dataPoints,
                borderColor: 'rgba(60,141,188,0.8)',
                backgroundColor: 'rgba(60,141,188,0.2)',
                fill: true, tension: 0.1,
                pointBackgroundColor: function (context) {
                    return (dataPoints[context.dataIndex] || 0) < 0 ? 'rgba(255,99,132,1)' : 'rgba(60,141,188,1)';
                },
                pointRadius: 4, pointHoverRadius: 6
            }]
        },
        options: {
            maintainAspectRatio: false,
            interaction: { mode: 'nearest', intersect: false },
            plugins: {
                tooltip: {
                    padding: 10,
                    callbacks: {
                        label: function (context) {
                            var d = historyData[context.dataIndex];
                            return [
                                'Balance: ' + d.after.toLocaleString(),
                                'Change: ' + (d.change > 0 ? '+' : '') + d.change.toLocaleString(),
                                'Before: ' + d.before.toLocaleString(),
                                d.desc
                            ];
                        }
                    }
                }
            },
            scales: { y: { beginAtZero: false } }
        }
    });
</script>
