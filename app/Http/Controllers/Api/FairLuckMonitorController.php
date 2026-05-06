<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CoreWallet;
use App\Models\FairLuckSetting;
use App\Models\FairLuckTransaction;
use App\Models\FairLuckWallet;
use App\Models\FairLuckWalletHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FairLuckMonitorController extends Controller
{
    public function dashboard()
    {
        // Set a strict 10s query timeout so heavy queries fail fast instead of 504
        try {
            DB::statement("SET SESSION MAX_EXECUTION_TIME=10000");
        } catch (\Throwable $e) {
            // MySQL < 5.7.8 doesn't support this, ignore
        }

        // Settings
        $settings = FairLuckSetting::pluck('value', 'key')->toArray();
        $appFee = (float) ($settings['fair_luck_app_fee_rate'] ?? 0.015);
        $receiverFee = (float) ($settings['fair_luck_receiver_fee_rate'] ?? 0.10);
        $ownerFee = (float) ($settings['fair_luck_owner_fee_rate'] ?? 0);
        $targetRtp = (float) ($settings['V7_target_rtp'] ?? 0.99);

        // Vault
        $vaultBalance = FairLuckWallet::where('wallet_type', 'global_vault')->value('balance') ?? 0;
        $redisBalance = FairLuckWallet::getRedisBalance(FairLuckWallet::TYPE_GLOBAL_VAULT);
        $appWallet = CoreWallet::where('name', 'app_wallet')->value('coins') ?? 0;

        // Zone
        $thresholds = [
            'min' => (int) ($settings['V7_wallet_min'] ?? 10000),
            'tight' => (int) ($settings['V7_wallet_tight'] ?? 50000),
            'target' => (int) ($settings['V7_wallet_target'] ?? 200000),
            'high' => (int) ($settings['V7_wallet_high'] ?? 500000),
        ];

        // Overall stats
        $allTime = DB::selectOne("
            SELECT COUNT(*) as total, SUM(CASE WHEN is_winner=1 THEN 1 ELSE 0 END) as wins,
                   SUM(bet_amount) as total_bet,
                   SUM(CASE WHEN is_winner=1 THEN profit_amount+bet_amount ELSE 0 END) as total_won,
                   SUM(profit_amount) as net_profit
            FROM fair_luck_transactions
        ");

        // Today stats
        $today = DB::selectOne("
            SELECT COUNT(*) as total, SUM(CASE WHEN is_winner=1 THEN 1 ELSE 0 END) as wins,
                   SUM(bet_amount) as total_bet,
                   SUM(CASE WHEN is_winner=1 THEN profit_amount+bet_amount ELSE 0 END) as total_won,
                   SUM(profit_amount) as net_profit,
                   COUNT(DISTINCT user_id) as unique_users
            FROM fair_luck_transactions WHERE created_at >= CURDATE()
        ");

        // Last hour
        $lastHour = DB::selectOne("
            SELECT COUNT(*) as total, SUM(CASE WHEN is_winner=1 THEN 1 ELSE 0 END) as wins,
                   SUM(bet_amount) as total_bet,
                   SUM(profit_amount) as net_profit,
                   COUNT(DISTINCT user_id) as unique_users
            FROM fair_luck_transactions WHERE created_at >= NOW() - INTERVAL 1 HOUR
        ");

        // Top winners today
        $topWinnersToday = DB::select("
            SELECT user_id, COUNT(*) as spins,
                   SUM(CASE WHEN is_winner=1 THEN 1 ELSE 0 END) as wins,
                   SUM(bet_amount) as total_bet,
                   SUM(CASE WHEN is_winner=1 THEN profit_amount+bet_amount ELSE 0 END) as total_won,
                   MAX(multiplier) as max_mult
            FROM fair_luck_transactions WHERE created_at >= CURDATE()
            GROUP BY user_id ORDER BY total_won DESC LIMIT 10
        ");

        // Biggest wins today
        $bigWinsToday = DB::select("
            SELECT user_id, bet_amount, multiplier, profit_amount, created_at
            FROM fair_luck_transactions
            WHERE is_winner=1 AND created_at >= CURDATE()
            ORDER BY profit_amount DESC LIMIT 15
        ");

        // Multiplier distribution today
        $multDist = DB::select("
            SELECT COALESCE(multiplier, 0) as mult,
                   COUNT(*) as cnt,
                   SUM(CASE WHEN is_winner=1 THEN profit_amount+bet_amount ELSE 0 END) as total_payout
            FROM fair_luck_transactions WHERE created_at >= CURDATE()
            GROUP BY mult ORDER BY mult
        ");

        // Vault history (last 200)
        $vaultHistory = FairLuckWalletHistory::where('wallet_type', 'global_vault')
            ->orderBy('created_at', 'desc')
            ->limit(200)
            ->get()->reverse()->values()
            ->map(fn($h) => [
                'time' => $h->created_at?->format('H:i:s') ?? '',
                'date' => $h->created_at?->format('m-d H:i') ?? '',
                'before' => (int) $h->balance_before,
                'after' => (int) $h->balance_after,
                'change' => (int) $h->amount,
                'desc' => $h->description ?? '',
            ]);

        // Hourly breakdown today
        $hourly = DB::select("
            SELECT HOUR(created_at) as hr, COUNT(*) as spins,
                   SUM(CASE WHEN is_winner=1 THEN 1 ELSE 0 END) as wins,
                   SUM(bet_amount) as bet, SUM(profit_amount) as profit
            FROM fair_luck_transactions WHERE created_at >= CURDATE()
            GROUP BY hr ORDER BY hr
        ");

        // Per-user RTP for active users today
        $userRtps = DB::select("
            SELECT user_id, COUNT(*) as spins,
                   SUM(bet_amount) as total_bet,
                   SUM(CASE WHEN is_winner=1 THEN profit_amount+bet_amount ELSE 0 END) as total_won
            FROM fair_luck_transactions WHERE created_at >= CURDATE()
            GROUP BY user_id HAVING spins >= 10
            ORDER BY spins DESC LIMIT 20
        ");

        return response(
            $this->renderHtml(
                $settings, $vaultBalance, $redisBalance, $appWallet, $thresholds,
                $allTime, $today, $lastHour, $topWinnersToday, $bigWinsToday,
                $multDist, $vaultHistory, $hourly, $userRtps,
                $appFee, $receiverFee, $ownerFee, $targetRtp
            ),
            200,
            ['Content-Type' => 'text/html']
        );
    }

    public function apiStats()
    {
        $vault = FairLuckWallet::getRedisBalance(FairLuckWallet::TYPE_GLOBAL_VAULT);
        $app = CoreWallet::where('name', 'app_wallet')->value('coins') ?? 0;
        $lastHour = DB::selectOne("
            SELECT COUNT(*) as total, SUM(CASE WHEN is_winner=1 THEN 1 ELSE 0 END) as wins,
                   SUM(bet_amount) as bet, SUM(profit_amount) as profit
            FROM fair_luck_transactions WHERE created_at >= NOW() - INTERVAL 1 HOUR
        ");
        $history = FairLuckWalletHistory::where('wallet_type', 'global_vault')
            ->orderBy('created_at', 'desc')->limit(200)
            ->get()->reverse()->values()
            ->map(fn($h) => ['t' => $h->created_at?->format('H:i:s'), 'v' => (int) $h->balance_after]);

        return response()->json(compact('vault', 'app', 'lastHour', 'history'));
    }

    private function renderHtml($settings, $vault, $redis, $appWallet, $t, $all, $today, $hour, $topWinners, $bigWins, $multDist, $vaultHistory, $hourly, $userRtps, $appFee, $rcvFee, $ownerFee, $targetRtp): string
    {
        $zone = $vault <= $t['min'] ? 'CRITICAL' : ($vault <= $t['tight'] ? 'TIGHT' : ($vault <= $t['target'] ? 'NORMAL' : ($vault <= $t['high'] ? 'GENEROUS' : 'DRAIN')));
        $zc = ['CRITICAL'=>'#dc3545','TIGHT'=>'#e67e22','NORMAL'=>'#3498db','GENEROUS'=>'#28a745','DRAIN'=>'#8e44ad'][$zone];
        $allRtp = ($all->total_bet ?? 0) > 0 ? round(($all->total_won ?? 0) / $all->total_bet * 100, 2) : 0;
        $todayRtp = ($today->total_bet ?? 0) > 0 ? round(($today->total_won ?? 0) / $today->total_bet * 100, 2) : 0;
        $hourRtp = ($hour->total_bet ?? 0) > 0 ? round((($hour->wins ?? 0) > 0 ? (($hour->net_profit ?? 0) + ($hour->total_bet ?? 0)) : 0) / max(1, $hour->total_bet) * 100, 2) : 0;
        $todayWinRate = ($today->total ?? 0) > 0 ? round(($today->wins ?? 0) / $today->total * 100, 1) : 0;
        $allWinRate = ($all->total ?? 0) > 0 ? round(($all->wins ?? 0) / $all->total * 100, 1) : 0;

        $vhJson = json_encode($vaultHistory);
        $zonesJson = json_encode($t);
        $hourlyJson = json_encode($hourly);

        // Top winners rows
        $winnersHtml = '';
        foreach ($topWinners as $w) {
            $rtp = $w->total_bet > 0 ? round($w->total_won / $w->total_bet * 100, 1) : 0;
            $rtpClass = $rtp > 100 ? 'text-success' : ($rtp > 80 ? 'text-warning' : 'text-danger');
            $winnersHtml .= "<tr><td>{$w->user_id}</td><td>{$w->spins}</td><td>{$w->wins}</td><td>" . number_format($w->total_bet) . "</td><td class='text-success'>" . number_format($w->total_won) . "</td><td>{$w->max_mult}x</td><td class='{$rtpClass}'>{$rtp}%</td></tr>";
        }

        // Big wins rows
        $bigWinsHtml = '';
        foreach ($bigWinsToday ?? $bigWins as $b) {
            $bigWinsHtml .= "<tr><td>{$b->user_id}</td><td>" . number_format($b->bet_amount) . "</td><td><strong>{$b->multiplier}x</strong></td><td class='text-success'>" . number_format($b->profit_amount) . "</td><td>{$b->created_at}</td></tr>";
        }

        // Multiplier dist rows
        $multHtml = '';
        $totalSpinsToday = array_sum(array_column($multDist, 'cnt'));
        foreach ($multDist as $m) {
            $pct = $totalSpinsToday > 0 ? round($m->cnt / $totalSpinsToday * 100, 2) : 0;
            $label = $m->mult == 0 ? '0x (loss)' : $m->mult . 'x';
            $multHtml .= "<tr><td>{$label}</td><td>" . number_format($m->cnt) . "</td><td>{$pct}%</td><td>" . number_format($m->total_payout) . "</td></tr>";
        }

        // User RTP rows
        $userRtpHtml = '';
        foreach ($userRtps as $u) {
            $rtp = $u->total_bet > 0 ? round($u->total_won / $u->total_bet * 100, 1) : 0;
            $cls = $rtp > 100 ? 'text-success' : ($rtp > 80 ? '' : 'text-danger');
            $userRtpHtml .= "<tr><td>{$u->user_id}</td><td>{$u->spins}</td><td>" . number_format($u->total_bet) . "</td><td>" . number_format($u->total_won) . "</td><td class='{$cls}'><strong>{$rtp}%</strong></td></tr>";
        }

        return <<<HTML
<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>V7 Monitor</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body{background:#0d1117;color:#c9d1d9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;font-size:13px}
.card{background:#161b22;border:1px solid #30363d;border-radius:8px}
.card-header{border-bottom:1px solid #30363d;font-weight:600}
.table{color:#c9d1d9;font-size:12px}.table th{border-color:#30363d;color:#8b949e}.table td{border-color:#21262d}
.stat-box{text-align:center;padding:16px;border-radius:8px}
.stat-value{font-size:1.8rem;font-weight:700}.stat-label{font-size:.75rem;color:#8b949e;text-transform:uppercase}
.badge-zone{font-size:1rem;padding:6px 16px;border-radius:6px}
.text-success{color:#3fb950!important}.text-danger{color:#f85149!important}.text-warning{color:#d29922!important}
.pulse{animation:pulse 2s infinite}@keyframes pulse{0%,100%{opacity:1}50%{opacity:.5}}
#lastUpdate{color:#8b949e;font-size:11px}
</style></head><body>
<div class="container-fluid py-3">

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0" style="color:#58a6ff">FairLuck V7 Live Monitor</h4>
<div><span class="pulse" style="color:#3fb950">●</span> <span id="lastUpdate">Updated: {$now}</span>
<button class="btn btn-sm btn-outline-secondary ms-2" onclick="location.reload()">Refresh</button></div>
</div>

<!-- Top Cards -->
<div class="row g-2 mb-3">
<div class="col-md-3"><div class="stat-box" style="background:{$zc}20;border:1px solid {$zc}">
<div class="stat-value" style="color:{$zc}">{$zone}</div>
<div class="stat-label">Vault Zone</div>
<div style="font-size:1.2rem;margin-top:4px">{$this->fmt($vault)} coins</div>
</div></div>
<div class="col-md-3"><div class="stat-box" style="background:#e6722020;border:1px solid #e67220">
<div class="stat-value" style="color:#e67220">{$this->fmt($appWallet)}</div>
<div class="stat-label">App Wallet</div>
</div></div>
<div class="col-md-2"><div class="stat-box" style="background:#58a6ff20;border:1px solid #58a6ff">
<div class="stat-value" style="color:#58a6ff">{$todayRtp}%</div>
<div class="stat-label">RTP Today</div>
</div></div>
<div class="col-md-2"><div class="stat-box" style="background:#3fb95020;border:1px solid #3fb950">
<div class="stat-value" style="color:#3fb950">{$todayWinRate}%</div>
<div class="stat-label">Win Rate Today</div>
</div></div>
<div class="col-md-2"><div class="stat-box" style="background:#bc8cff20;border:1px solid #bc8cff">
<div class="stat-value" style="color:#bc8cff">{$this->fmt($today->total ?? 0)}</div>
<div class="stat-label">Spins Today</div>
</div></div>
</div>

<!-- Second Row: Config + Last Hour -->
<div class="row g-2 mb-3">
<div class="col-md-4"><div class="card"><div class="card-header">Engine Config</div><div class="card-body p-2">
<table class="table table-sm mb-0">
<tr><td>App Fee</td><td><strong>{$this->pct($appFee)}</strong></td></tr>
<tr><td>Receiver</td><td><strong>{$this->pct($rcvFee)}</strong></td></tr>
<tr><td>Host</td><td><strong>{$this->pct($ownerFee)}</strong></td></tr>
<tr><td>Target RTP</td><td><strong>{$this->pct($targetRtp)}</strong></td></tr>
<tr><td>Redis Balance</td><td>{$this->fmt($redis)}</td></tr>
<tr><td>DB Balance</td><td>{$this->fmt($vault)}</td></tr>
</table></div></div></div>
<div class="col-md-4"><div class="card"><div class="card-header">Last Hour</div><div class="card-body p-2">
<table class="table table-sm mb-0">
<tr><td>Spins</td><td><strong>{$this->fmt($hour->total ?? 0)}</strong></td></tr>
<tr><td>Wins</td><td>{$this->fmt($hour->wins ?? 0)}</td></tr>
<tr><td>Unique Users</td><td>{$this->fmt($hour->unique_users ?? 0)}</td></tr>
<tr><td>Total Bet</td><td>{$this->fmt($hour->total_bet ?? 0)}</td></tr>
<tr><td>Net P&L</td><td class="text-danger">{$this->fmt($hour->net_profit ?? 0)}</td></tr>
</table></div></div></div>
<div class="col-md-4"><div class="card"><div class="card-header">All Time</div><div class="card-body p-2">
<table class="table table-sm mb-0">
<tr><td>Total Spins</td><td><strong>{$this->fmt($all->total ?? 0)}</strong></td></tr>
<tr><td>Win Rate</td><td>{$allWinRate}%</td></tr>
<tr><td>RTP</td><td><strong>{$allRtp}%</strong></td></tr>
<tr><td>Total Bet</td><td>{$this->fmt($all->total_bet ?? 0)}</td></tr>
<tr><td>System Net</td><td>{$this->fmt($all->net_profit ?? 0)}</td></tr>
</table></div></div></div>
</div>

<!-- Vault Chart -->
<div class="card mb-3"><div class="card-header">Vault Balance Timeline</div>
<div class="card-body"><canvas id="vaultChart" style="height:280px"></canvas></div></div>

<!-- Hourly Chart -->
<div class="card mb-3"><div class="card-header">Hourly Activity Today</div>
<div class="card-body"><canvas id="hourlyChart" style="height:200px"></canvas></div></div>

<!-- Tables Row -->
<div class="row g-2 mb-3">
<div class="col-md-6"><div class="card"><div class="card-header">Top Winners Today</div><div class="card-body p-0">
<table class="table table-sm mb-0"><thead><tr><th>User</th><th>Spins</th><th>Wins</th><th>Bet</th><th>Won</th><th>Max</th><th>RTP</th></tr></thead>
<tbody>{$winnersHtml}</tbody></table></div></div></div>
<div class="col-md-6"><div class="card"><div class="card-header">Biggest Wins Today</div><div class="card-body p-0">
<table class="table table-sm mb-0"><thead><tr><th>User</th><th>Bet</th><th>Mult</th><th>Profit</th><th>Time</th></tr></thead>
<tbody>{$bigWinsHtml}</tbody></table></div></div></div>
</div>

<div class="row g-2 mb-3">
<div class="col-md-4"><div class="card"><div class="card-header">Multiplier Distribution Today</div><div class="card-body p-0">
<table class="table table-sm mb-0"><thead><tr><th>Mult</th><th>Count</th><th>%</th><th>Payout</th></tr></thead>
<tbody>{$multHtml}</tbody></table></div></div></div>
<div class="col-md-4"><div class="card"><div class="card-header">Hourly Breakdown</div><div class="card-body p-0">
<table class="table table-sm mb-0"><thead><tr><th>Hour</th><th>Spins</th><th>Wins</th><th>Win%</th><th>Bet</th><th>P&L</th></tr></thead>
<tbody>{$hourlyHtml}</tbody></table></div></div></div>
<div class="col-md-4"><div class="card"><div class="card-header">Per-User RTP Today (10+ spins)</div><div class="card-body p-0">
<table class="table table-sm mb-0"><thead><tr><th>User</th><th>Spins</th><th>Bet</th><th>Won</th><th>RTP</th></tr></thead>
<tbody>{$userRtpHtml}</tbody></table></div></div></div>
</div>

</div>
<script>
var vhData={$vhJson}, zones={$zonesJson}, hourlyData={$hourlyJson};

// Vault Chart
new Chart(document.getElementById('vaultChart'),{type:'line',data:{
labels:vhData.map(d=>d.time),
datasets:[
{label:'Balance',data:vhData.map(d=>d.after),borderColor:'#58a6ff',backgroundColor:'#58a6ff20',fill:true,tension:.2,pointRadius:vhData.length>100?0:2,borderWidth:2},
{label:'CRITICAL',data:Array(vhData.length).fill(zones.min),borderColor:'#f8514940',borderDash:[4,4],borderWidth:1,pointRadius:0,fill:false},
{label:'TIGHT',data:Array(vhData.length).fill(zones.tight),borderColor:'#d2992240',borderDash:[4,4],borderWidth:1,pointRadius:0,fill:false},
{label:'TARGET',data:Array(vhData.length).fill(zones.target),borderColor:'#58a6ff40',borderDash:[6,3],borderWidth:1,pointRadius:0,fill:false},
{label:'GENEROUS',data:Array(vhData.length).fill(zones.high),borderColor:'#3fb95040',borderDash:[4,4],borderWidth:1,pointRadius:0,fill:false},
]},options:{maintainAspectRatio:false,plugins:{legend:{labels:{color:'#8b949e',font:{size:10}}}},
scales:{x:{ticks:{color:'#8b949e',maxTicksLimit:15,font:{size:10}}},y:{ticks:{color:'#8b949e',callback:v=>v>=1e3?(v/1e3).toFixed(0)+'k':v}}}}});

// Hourly Chart
if(hourlyData.length){new Chart(document.getElementById('hourlyChart'),{type:'bar',data:{
labels:hourlyData.map(h=>String(h.hr).padStart(2,'0')+':00'),
datasets:[
{label:'Spins',data:hourlyData.map(h=>h.spins),backgroundColor:'#58a6ff60',borderRadius:4,yAxisID:'y'},
{label:'Wins',data:hourlyData.map(h=>h.wins),backgroundColor:'#3fb95060',borderRadius:4,yAxisID:'y'},
]},options:{maintainAspectRatio:false,plugins:{legend:{labels:{color:'#8b949e'}}},
scales:{x:{ticks:{color:'#8b949e'}},y:{ticks:{color:'#8b949e'},position:'left'}}}});}

// Auto-refresh every 60s
setTimeout(()=>location.reload(),60000);
</script></body></html>
HTML;
    }

    private function fmt($n): string { return number_format((float)$n); }
    private function pct($v): string { return round((float)$v * 100, 2) . '%'; }
}
