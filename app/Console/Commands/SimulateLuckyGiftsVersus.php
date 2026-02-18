<?php

namespace App\Console\Commands;

use App\Models\Gift;
use App\Models\User;
use App\Models\UserLuckProfile;
use App\Services\FairLuck\FairLuckService3;
use App\Services\FairLuck\LossLedger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SimulateLuckyGiftsVersus extends Command
{
    private const LABELS = ['A', 'B', 'C', 'D', 'E'];

    protected $signature = 'simulate:lucky-gifts-versus {gift_id?} {--rounds=30} {--balance=30000} {--player-count=3} {--price=} {--userA=} {--userB=} {--userC=} {--userD=} {--userE=}';
    protected $description = 'Simulate Service 3 among multiple users and generate an HTML timeline report';

    public function handle(FairLuckService3 $fairLuckService, LossLedger $lossLedger)
    { 
        $rounds = max(1, (int) $this->option('rounds'));
        $startingBalance = max(1, (int) $this->option('balance'));
        $playerCount = min(count(self::LABELS), max(2, (int) $this->option('player-count')));

        
        $gift = $this->resolveGift($this->argument('gift_id'));
        if (!$gift) {
            $this->error('No lucky gift found. Pass a gift_id or enable at least one type 6 gift.');
            return Command::FAILURE;
        }

        $overridePrice = $this->option('price');
        if ($overridePrice !== null) {
            $gift = clone $gift;
            $gift->price = max(1, (int) $overridePrice);
        }

        $players = [];
        $labels = array_slice(self::LABELS, 0, $playerCount);
        foreach ($labels as $label) {
            $optionName = 'user' . $label;
            $players[$label] = $this->prepareUser($label, $this->option($optionName), $startingBalance);
        }

        foreach ($players as $player) {
            $this->ensureLuckProfile($player);
        }

        $stats = [];
        foreach ($players as $label => $player) {
            $stats[$label] = [
                'label' => $label,
                'user' => $player,
                'initial' => (int) $player->di,
                'spent' => 0,
                'won' => 0,
                'bets' => 0,
                'wins' => 0,
                'balance' => (int) $player->di,
                'house_cut' => 0,
                'drained_at_round' => null,
                'high_multipliers' => 0,
                'max_multiplier' => 0,
            ];
        }

        $history = [];
        $step = 0;
        $appFlow = [
            'spent' => 0,
            'won' => 0,
        ];
        $playerLossLedger = [];
        foreach ($labels as $label) {
            $playerLossLedger[$label] = 0;
        }
        $storageTimeline = [];

        for ($round = 1; $round <= $rounds; $round++) {
            foreach ($players as $label => $player) {
                $player->refresh();
                $currentBalance = (int) $player->di;

                if ($currentBalance < $gift->price) {
                    $this->markPlayerAsDrained($player, $stats[$label], $round);
                    continue;
                }

                $storageBefore = $lossLedger->poolBalance();
                $profileBefore = $this->ensureLuckProfile($player);
                $deviationBefore = (float) ($profileBefore->current_deviation ?? 0.0);

                $step++;
                $balanceBefore = $currentBalance;
                $currentBalance -= $gift->price;

                $result = $fairLuckService->processBet($player, $gift, $gift->price);

                $winAmount = $result->isWinner ? (int) round($result->multiplier * $gift->price) : 0;
                $currentBalance += $winAmount;

                $houseCutApplied = (int) ($result->houseCut ?? 0);
                if ($houseCutApplied > 0) {
                    $houseCutApplied = min($houseCutApplied, max(0, $currentBalance));
                    $currentBalance -= $houseCutApplied;
                    $stats[$label]['house_cut'] += $houseCutApplied;
                }

                $player->di = $currentBalance;
                $player->save();

                $profileAfter = $this->ensureLuckProfile($player)->fresh();
                $deviationAfter = (float) ($profileAfter?->current_deviation ?? 0.0);
                $trend = $this->deviationTrend($deviationBefore, $deviationAfter);

                $stats[$label]['spent'] += $gift->price;
                $stats[$label]['won'] += $winAmount;
                $stats[$label]['bets'] += 1;
                $stats[$label]['balance'] = $currentBalance;
                if ($result->isWinner) {
                    $stats[$label]['wins'] += 1;
                    if ($result->multiplier >= 250) {
                        $stats[$label]['high_multipliers'] += 1;
                    }
                    if ($result->multiplier > $stats[$label]['max_multiplier']) {
                        $stats[$label]['max_multiplier'] = $result->multiplier;
                    }
                }
                if ($currentBalance < $gift->price && $stats[$label]['drained_at_round'] === null) {
                    $stats[$label]['drained_at_round'] = $round;
                }

                $appFlow['spent'] += $gift->price;
                if ($winAmount > 0) {
                    $appFlow['won'] += $winAmount;
                }
                $appNet = $appFlow['spent'] - $appFlow['won'];

                $storageAfter = $lossLedger->poolBalance();
                $storageDelta = $storageAfter - $storageBefore;

                $netContribution = $gift->price - $winAmount;
                $playerLossLedger[$label] = max(0, ($playerLossLedger[$label] ?? 0) + $netContribution);
                $topLossLabel = null;
                $topLossAmount = 0;
                foreach ($playerLossLedger as $lossLabel => $lossValue) {
                    if ($lossValue > $topLossAmount) {
                        $topLossLabel = $lossLabel;
                        $topLossAmount = $lossValue;
                    }
                }
                $topLossUser = $topLossLabel ? ($players[$topLossLabel]->name ?? $topLossLabel) . ' (' . $topLossLabel . ')' : '—';
                $aggregatePlayerLosses = array_sum($playerLossLedger);

                $storageTimeline[] = [
                    'step' => $step,
                    'round' => $round,
                    'storage_after' => $storageAfter,
                    'storage_delta' => $storageDelta,
                    'top_loss_label' => $topLossLabel,
                    'top_loss_user' => $topLossUser,
                    'top_loss_amount' => $topLossAmount,
                    'aggregate_losses' => $aggregatePlayerLosses,
                    'app_net' => $appNet,
                ];

                $history[] = [
                    'step' => $step,
                    'round' => $round,
                    'label' => $label,
                    'user' => $player->name,
                    'outcome' => $result->isWinner ? 'WIN' : 'LOSS',
                    'multiplier' => $result->isWinner ? $result->multiplier : '-',
                    'win' => $winAmount,
                    'bet' => $gift->price,
                    'house_cut' => $houseCutApplied,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $currentBalance,
                    'deviation_before' => $deviationBefore,
                    'deviation_after' => $deviationAfter,
                    'trend' => $trend,
                    'mood' => $result->mood,
                    'storage_before' => $storageBefore,
                    'storage_after' => $storageAfter,
                    'storage_delta' => $storageDelta,
                    'storage_event' => $storageDelta > 0 ? 'DEPOSIT' : ($storageDelta < 0 ? 'WITHDRAW' : 'NEUTRAL'),
                    'app_spent_total' => $appFlow['spent'],
                    'app_won_total' => $appFlow['won'],
                    'app_net' => $appNet,
                ];
            }

            if ($this->allPlayersExhausted($stats, $gift->price)) {
                break;
            }
        }

        if (empty($history)) {
            $this->warn('No bets were processed. Ensure both users have enough balance.');
            return Command::SUCCESS;
        }

        $reportPath = $this->generateHtmlReport($gift, $stats, $history, $storageTimeline);

        $this->info(str_repeat('-', 40));
        $this->info('Head-to-head simulation finished.');
        foreach ($stats as $summary) {
            $rtp = $summary['spent'] > 0 ? ($summary['won'] / $summary['spent']) * 100 : 0;
            $this->line(sprintf(
                '%s (%s) => Bets: %d | Wins: %d | RTP: %.2f%% | House Cut: %s | Final Balance: %s',
                $summary['user']->name,
                $summary['label'],
                $summary['bets'],
                $summary['wins'],
                $rtp,
                number_format($summary['house_cut']),
                number_format($summary['balance'])
            ));
        }
        $this->info('Report: ' . $reportPath);
        $this->info(str_repeat('-', 40));

        return Command::SUCCESS;
    }

    private function resolveGift(?int $giftId): ?Gift
    {
        if ($giftId) {
            return Gift::with('luckyGift')->find($giftId);
        }

        return Gift::with('luckyGift')
            ->where('type', 6)
            ->where('enable', 1)
            ->orderByDesc('id')
            ->first();
    }

    private function prepareUser(string $label, ?string $userId, int $balance): User
    {
        if ($userId) {
            $user = User::findOrFail($userId);
            $user->di = $balance;
            $user->save();
            return $user->fresh();
        }

        return User::factory()->create([
            'di' => $balance,
            'name' => 'VersusPlayer_' . $label . '_' . now()->timestamp,
        ]);
    }

    private function ensureLuckProfile(User $user): UserLuckProfile
    {
        $profile = UserLuckProfile::firstOrCreate(['user_id' => $user->id]);
        if (!$profile->is_legacy_user) {
            $profile->is_legacy_user = true;
        }
        if ($profile->current_deviation === null) {
            $profile->current_deviation = 0;
        }
        $profile->save();

        return $profile;
    }

    private function deviationTrend(float $before, float $after): string
    {
        $delta = $after - $before;
        if ($delta > 0.0001) {
            return 'UP';
        }
        if ($delta < -0.0001) {
            return 'DOWN';
        }
        return 'FLAT';
    }

    private function markPlayerAsDrained(User $player, array &$stat, int $round): void
    {
        if ($stat['drained_at_round'] === null) {
            $stat['drained_at_round'] = $round;
        }

        $remaining = max(0, (int) $player->di);
        $stat['balance'] = $remaining;
    }

    private function allPlayersExhausted(array $stats, int $betPrice): bool
    {
        foreach ($stats as $summary) {
            if ($summary['balance'] >= $betPrice) {
                return false;
            }
        }

        return true;
    }

    private function generateHtmlReport(Gift $gift, array $stats, array $history, array $storageTimeline): string
    {
        $totals = [
            'initial' => 0,
            'spent' => 0,
            'won' => 0,
            'house_cut' => 0,
            'final' => 0,
            'available' => 0,
        ];

        $summaryCards = '';
        foreach ($stats as $summary) {
            $totals['initial'] += $summary['initial'];
            $totals['spent'] += $summary['spent'];
            $totals['won'] += $summary['won'];
            $totals['house_cut'] += $summary['house_cut'];
            $totals['final'] += $summary['balance'];
            $totalAvailable = $summary['initial'] + $summary['won'];
            $totals['available'] += $totalAvailable;

            $rtp = $summary['spent'] > 0 ? ($summary['won'] / $summary['spent']) * 100 : 0;
            $netFlow = $summary['won'] - $summary['spent'];
            $balanceDelta = $summary['balance'] - $summary['initial'];
            $playerEquation = number_format($summary['initial']) . ' + ' . number_format($netFlow) . ' - ' . number_format($summary['house_cut']) . ' = ' . number_format($summary['balance']);
            $drainRound = $summary['drained_at_round'] ? (string) $summary['drained_at_round'] : 'لم ينفد بعد';
            $maxMultiplierDisplay = $summary['max_multiplier'] > 0 ? number_format($summary['max_multiplier']) . 'x' : 'لا شيء';

            $summaryCards .= "
                <div class='col-md-4 mb-4'>
                    <div class='card shadow-sm h-100'>
                        <div class='card-body'>
                            <h4 class='card-title'>المستخدم {$summary['user']->name} ({$summary['label']})</h4>
                            <p class='mb-1 text-secondary'>الرصيد الابتدائي: " . number_format($summary['initial']) . "</p>
                            <div class='row text-center g-3'>
                                <div class='col-6 col-lg-4'>
                                    <small>المحاولات</small>
                                    <p class='h5 mb-0'>{$summary['bets']}</p>
                                </div>
                                <div class='col-6 col-lg-4'>
                                    <small>مرات الفوز</small>
                                    <p class='h5 mb-0'>{$summary['wins']}</p>
                                </div>
                                <div class='col-6 col-lg-4'>
                                    <small>الدور الذي نفد فيه</small>
                                    <p class='h5 mb-0'>{$drainRound}</p>
                                </div>
                                <div class='col-6 col-lg-4'>
                                    <small>إجمالي الرهانات</small>
                                    <p class='h5 mb-0'>" . number_format($totalAvailable) . "</p>
                                </div>
                                <div class='col-6 col-lg-4'>
                                    <small>إجمالي المكاسب</small>
                                    <p class='h5 text-success mb-0'>" . number_format($summary['won']) . "</p>
                                </div>
                                <div class='col-6 col-lg-4'>
                                    <small>الرصيد النهائي</small>
                                    <p class='h5 mb-0'>" . number_format($summary['balance']) . "</p>
                                </div>
                                <div class='col-6 col-lg-4'>
                                    <small>RTP</small>
                                    <p class='h5 mb-0'>" . number_format($rtp, 2) . "%</p>
                                </div>
                               
                                <div class='col-6 col-lg-4'>
                                    <small>مضاعفات ≥250</small>
                                    <p class='h5 mb-0'>" . number_format($summary['high_multipliers']) . "</p>
                                </div>
                                <div class='col-6 col-lg-4'>
                                    <small>أعلى مضاعف</small>
                                    <p class='h5 mb-0'>{$maxMultiplierDisplay}</p>
                                </div>
                            </div>
                            <div class='mt-3'>
                                
                            </div>
                        </div>
                    </div>
                </div>";
        }

        $playerFilterOptions = "<option value='all'>الكل</option>";
        $summaryTableRows = '';
        foreach ($stats as $summary) {
            $netFlow = $summary['won'] - $summary['spent'];
            $drainRound = $summary['drained_at_round'] ? (string) $summary['drained_at_round'] : 'لم ينفد بعد';
            $maxMultiplierCell = $summary['max_multiplier'] > 0 ? number_format($summary['max_multiplier']) . 'x' : '—';
            $totalAvailable = $summary['initial'] + $summary['won'];
            $playerFilterOptions .= "<option value='{$summary['label']}'>" . e($summary['user']->name) . " ({$summary['label']})</option>";
            $summaryTableRows .= "
                <tr>
                    <td>{$summary['user']->name} ({$summary['label']})</td>
                    <td>" . number_format($summary['bets']) . "</td>
                    <td>" . number_format($summary['wins']) . "</td>
                    <td>" . number_format($summary['high_multipliers']) . "</td>
                    <td>" . number_format($summary['won']) . "</td>
                    <td>" . number_format($totalAvailable) . "</td>
                    <td>" . number_format($netFlow) . "</td>
                    <td>" . number_format($summary['house_cut']) . "</td>
                    <td>" . number_format($summary['balance']) . "</td>
                    <td>{$drainRound}</td>
                    <td>" . number_format($summary['spent'] > 0 ? ($summary['won'] / $summary['spent']) * 100 : 0, 2) . "%</td>
                    <td>{$maxMultiplierCell}</td>
                </tr>";
        }

        $historyRows = '';
        $storageRows = '';
        $storageStats = [
            'total_deposit' => 0,
            'deposit_count' => 0,
            'max_deposit' => null,
            'total_withdraw' => 0,
            'withdraw_count' => 0,
            'max_withdraw' => null,
            'max_balance' => null,
            'min_balance' => null,
            'drain_events' => [],
            'refill_events' => [],
        ];
        foreach ($history as $event) {
            $outcomeBadge = $event['outcome'] === 'WIN'
                ? "<span class='badge bg-success'>فوز</span>"
                : "<span class='badge bg-danger'>خسارة</span>";
            $multiplierDisplay = is_numeric($event['multiplier'])
                ? number_format((float) $event['multiplier']) . 'x'
                : '—';
            $trendLabel = match ($event['trend']) {
                'UP' => "<span class='text-success'>↑ UP</span>",
                'DOWN' => "<span class='text-danger'>↓ DOWN</span>",
                default => "<span class='text-muted'>FLAT</span>",
            };
            $historyRows .= "
                <tr data-filterable-row='true' data-player='{$event['label']}'>
                    <td>" . number_format($event['step']) . "</td>
                    <td>" . number_format($event['round']) . "</td>
                    <td>" . e($event['user']) . " ({$event['label']})</td>
                    <td>{$outcomeBadge}</td>
                    <td>{$multiplierDisplay}</td>
                    <td>" . number_format($event['deviation_before'], 4) . "</td>
                    <td>" . number_format($event['deviation_after'], 4) . "</td>
                    <td>" . number_format($event['bet']) . "</td>
                    <td class='text-success'>" . number_format($event['win']) . "</td>
                    <td>" . number_format($event['house_cut']) . "</td>
                    <td>" . number_format($event['balance_before']) . "</td>
                    <td>" . number_format($event['balance_after']) . "</td>
                    <td>{$trendLabel}</td>
                    <td>" . e($event['mood']) . "</td>
                </tr>";

            $storageBadge = match (true) {
                $event['storage_delta'] > 0 => "<span class='badge bg-warning text-dark'>تغذية الجاك بوت</span>",
                $event['storage_delta'] < 0 => "<span class='badge bg-info text-dark'>صرف من الخزان</span>",
                default => "<span class='badge bg-secondary'>ثابت</span>",
            };
            $storageDelta = (int) $event['storage_delta'];
            $storageDeltaPrefix = $storageDelta > 0 ? '+' : '';
            $storageDeltaDisplay = $storageDeltaPrefix . number_format($storageDelta);
            $storageDeltaClass = $storageDelta > 0 ? 'text-success fw-semibold' : ($storageDelta < 0 ? 'text-danger fw-semibold' : 'text-muted');
            $appNet = (int) $event['app_net'];
            $appNetPrefix = $appNet > 0 ? '+' : '';
            $appNetClass = $appNet >= 0 ? 'text-success fw-semibold' : 'text-danger fw-semibold';
            if ($storageDelta > 0) {
                $storageStats['total_deposit'] += $storageDelta;
                $storageStats['deposit_count'] += 1;
                if ($storageStats['max_deposit'] === null || $storageDelta > $storageStats['max_deposit']['amount']) {
                    $storageStats['max_deposit'] = [
                        'amount' => $storageDelta,
                        'step' => $event['step'],
                        'round' => $event['round'],
                        'player' => $event['user'] . " ({$event['label']})",
                    ];
                }
                if ($event['storage_before'] <= 0) {
                    $storageStats['refill_events'][] = [
                        'step' => $event['step'],
                        'round' => $event['round'],
                        'player' => $event['user'] . " ({$event['label']})",
                        'amount' => $storageDelta,
                    ];
                }
            } elseif ($storageDelta < 0) {
                $absDelta = abs($storageDelta);
                $storageStats['total_withdraw'] += $absDelta;
                $storageStats['withdraw_count'] += 1;
                if ($storageStats['max_withdraw'] === null || $absDelta > $storageStats['max_withdraw']['amount']) {
                    $storageStats['max_withdraw'] = [
                        'amount' => $absDelta,
                        'step' => $event['step'],
                        'round' => $event['round'],
                        'player' => $event['user'] . " ({$event['label']})",
                    ];
                }
            }

            $storageAfter = (int) $event['storage_after'];
            if ($storageStats['max_balance'] === null || $storageAfter > $storageStats['max_balance']['amount']) {
                $storageStats['max_balance'] = [
                    'amount' => $storageAfter,
                    'step' => $event['step'],
                    'round' => $event['round'],
                    'player' => $event['user'] . " ({$event['label']})",
                ];
            }
            if ($storageStats['min_balance'] === null || $storageAfter < $storageStats['min_balance']['amount']) {
                $storageStats['min_balance'] = [
                    'amount' => $storageAfter,
                    'step' => $event['step'],
                    'round' => $event['round'],
                    'player' => $event['user'] . " ({$event['label']})",
                ];
            }
            if ($storageAfter <= 0) {
                $storageStats['drain_events'][] = [
                    'step' => $event['step'],
                    'round' => $event['round'],
                    'player' => $event['user'] . " ({$event['label']})",
                ];
            }
            $storageRows .= "
                <tr data-filterable-row='true' data-player='{$event['label']}'>
                    <td>" . number_format($event['step']) . "</td>
                    <td>" . number_format($event['round']) . "</td>
                    <td>" . e($event['user']) . " ({$event['label']})</td>
                    <td>{$outcomeBadge}</td>
                    <td>" . number_format($event['bet']) . "</td>
                    <td class='text-success'>" . number_format($event['win']) . "</td>
                    <td>" . number_format($event['deviation_before'], 4) . "</td>
                    <td>" . number_format($event['deviation_after'], 4) . "</td>
                    <td>{$trendLabel}</td>
                    <td>{$storageBadge}</td>
                    <td class='{$storageDeltaClass}'> {$storageDeltaDisplay}</td>
                    <td>" . number_format($event['storage_after']) . "</td>
                    <td>" . number_format($event['app_spent_total']) . "</td>
                    <td class='text-success'>" . number_format($event['app_won_total']) . "</td>
                    <td class='{$appNetClass}'> {$appNetPrefix}" . number_format($appNet) . "</td>
                </tr>";
        }

        if ($historyRows === '') {
            $historyRows = "<tr><td colspan='12' class='text-center text-muted'>لا توجد طلبات مسجلة</td></tr>";
        }

        $lastRound = $history ? $history[count($history) - 1]['round'] : 0;
        $totals['net'] = $totals['won'] - $totals['spent'];
        $globalRtp = $totals['spent'] > 0 ? ($totals['won'] / $totals['spent']) * 100 : 0;
        $scenarioEquation = number_format($totals['initial']) . ' + ' . number_format($totals['net']) . ' - ' . number_format($totals['house_cut']) . ' = ' . number_format($totals['final']);
        $playersCount = count($stats);

        $scenarioOverview = "";

        $renderEventsList = static function (array $events): string {
            if (empty($events)) {
                return "<span class='text-muted'>لا توجد سجلات</span>";
            }

            $items = array_map(function ($event) {
                return "<li class='small'>#" . number_format($event['step']) . " | دور " . number_format($event['round']) . " | " . e($event['player']) . '</li>';
            }, $events);

            return '<ul class="mb-0 ps-3">' . implode('', $items) . '</ul>';
        };

        $maxDepositDisplay = $storageStats['max_deposit']
            ? number_format($storageStats['max_deposit']['amount']) . ' عند الطلب #' . number_format($storageStats['max_deposit']['step']) . ' (دور ' . number_format($storageStats['max_deposit']['round']) . ', ' . e($storageStats['max_deposit']['player']) . ')'
            : '—';
        $maxWithdrawDisplay = $storageStats['max_withdraw']
            ? number_format($storageStats['max_withdraw']['amount']) . ' عند الطلب #' . number_format($storageStats['max_withdraw']['step']) . ' (دور ' . number_format($storageStats['max_withdraw']['round']) . ', ' . e($storageStats['max_withdraw']['player']) . ')'
            : '—';
        $maxBalanceDisplay = $storageStats['max_balance']
            ? number_format($storageStats['max_balance']['amount']) . ' عند الطلب #' . number_format($storageStats['max_balance']['step']) . ' (دور ' . number_format($storageStats['max_balance']['round']) . ', ' . e($storageStats['max_balance']['player']) . ')'
            : '—';
        $minBalanceDisplay = $storageStats['min_balance']
            ? number_format($storageStats['min_balance']['amount']) . ' عند الطلب #' . number_format($storageStats['min_balance']['step']) . ' (دور ' . number_format($storageStats['min_balance']['round']) . ', ' . e($storageStats['min_balance']['player']) . ')'
            : '—';

        $storageStatsRows = "
            <tr>
                <td>إجمالي التخزين من خسائر اللاعبين</td>
                <td>" . number_format($storageStats['total_deposit']) . "</td>
                <td>تم عبر {$storageStats['deposit_count']} عملية تغذية.</td>
            </tr>
            <tr>
                <td>أكبر تغذية مفردة</td>
                <td>{$maxDepositDisplay}</td>
                <td></td>
            </tr>
            <tr>
                <td>إجمالي السحوبات لصرف الجوائز</td>
                <td>" . number_format($storageStats['total_withdraw']) . "</td>
                <td>تم عبر {$storageStats['withdraw_count']} عملية سحب.</td>
            </tr>
            <tr>
                <td>أكبر عملية سحب</td>
                <td>{$maxWithdrawDisplay}</td>
                <td></td>
            </tr>
            <tr>
                <td>أعلى رصيد وصلت له المحفظة</td>
                <td>{$maxBalanceDisplay}</td>
                <td></td>
            </tr>
            <tr>
                <td>أدنى رصيد / لحظة الاقتراب من الفراغ</td>
                <td>{$minBalanceDisplay}</td>
                <td></td>
            </tr>
            <tr>
                <td>لحظات إعادة التعبئة بعد الفراغ</td>
                <td colspan='2'>{$renderEventsList($storageStats['refill_events'])}</td>
            </tr>
            <tr>
                <td>لحظات تفريغ المحفظة</td>
                <td colspan='2'>{$renderEventsList($storageStats['drain_events'])}</td>
            </tr>";

        $storageMilestones = [];
        if (!empty($storageTimeline)) {
            $nextThreshold = 5000;
            $recordedSteps = [];
            foreach ($storageTimeline as $point) {
                $shouldRecord = false;
                if ($point['step'] % 50 === 0) {
                    $shouldRecord = true;
                }
                while ($point['storage_after'] >= $nextThreshold) {
                    $shouldRecord = true;
                    $nextThreshold += 5000;
                }
                if ($shouldRecord && !isset($recordedSteps[$point['step']])) {
                    $storageMilestones[] = $point;
                    $recordedSteps[$point['step']] = true;
                }
            }
            if (empty($storageMilestones)) {
                $storageMilestones[] = end($storageTimeline);
            } else {
                $lastRecorded = end($storageMilestones);
                $finalPoint = end($storageTimeline);
                if ($lastRecorded && $finalPoint && $lastRecorded['step'] !== $finalPoint['step']) {
                    $storageMilestones[] = $finalPoint;
                }
            }
        }

        $storageMilestonesRows = '';
        foreach ($storageMilestones as $milestone) {
            $deltaClass = $milestone['storage_delta'] > 0 ? 'text-success' : ($milestone['storage_delta'] < 0 ? 'text-danger' : 'text-muted');
            $deltaPrefix = $milestone['storage_delta'] > 0 ? '+' : '';
            $topLossDisplay = $milestone['top_loss_user'] !== '—'
                ? e($milestone['top_loss_user']) . ' — ' . number_format($milestone['top_loss_amount']) . ' خسارة تراكمية'
                : '—';
            $storageMilestonesRows .= "
                <tr>
                    <td>" . number_format($milestone['step']) . "</td>
                    <td>" . number_format($milestone['round']) . "</td>
                    <td>" . number_format($milestone['storage_after']) . "</td>
                    <td class='{$deltaClass}'> {$deltaPrefix}" . number_format($milestone['storage_delta']) . "</td>
                    <td>" . number_format($milestone['aggregate_losses']) . "</td>
                    <td>{$topLossDisplay}</td>
                    <td>" . number_format($milestone['app_net']) . "</td>
                </tr>";
        }
        if ($storageMilestonesRows === '') {
            $storageMilestonesRows = "<tr><td colspan='7' class='text-muted text-center'>لا توجد نقاط مختارة</td></tr>";
        }

        $storageTableBlock = "
        <div class='card shadow-sm mt-4'>
            <div class='card-body'>
                <div class='row g-3 align-items-end mb-4'>
                    <div class='col-12 col-lg-6'>
                        <h2 class='h4 mb-1'>مراقبة تخزين التطبيق والجاك بوت</h2>
                        <p class='text-muted mb-0'>توضح هذه الجدول أين يخزن التطبيق الخسائر الإضافية ومتى يقوم بالسحب، مع تتبع صافي وضع التطبيق بعد كل إرسال هدية.</p>
                    </div>
                    <div class='col-12 col-lg-4 ms-lg-auto'>
                        <label for='playerFilter' class='form-label small text-muted mb-1'>فلترة حسب المستخدم (يؤثر على كل الجداول)</label>
                        <select id='playerFilter' class='form-select form-select-sm'>
                            {$playerFilterOptions}
                        </select>
                    </div>
                </div>
                <div class='table-responsive'>
                    <table class='table table-striped align-middle small' data-filterable-table='true'>
                        <thead class='table-light'>
                            <tr>
                                <th>#</th>
                                <th>الدور</th>
                                <th>المستخدم</th>
                                <th>النتيجة</th>
                                <th>الرهان</th>
                                <th>الربح</th>
                                <th>انحراف قبل</th>
                                <th>انحراف بعد</th>
                                <th>اتجاه</th>
                                <th>حالة التخزين</th>
                                <th>التغير في المخزون</th>
                                <th>الرصيد المخزن</th>
                                <th>إجمالي مدفوعات التطبيق</th>
                                <th>إجمالي مكاسب اللاعبين</th>
                                <th>صافي التطبيق</th>
                            </tr>
                        </thead>
                        <tbody>
                            {$storageRows}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>";

        $storageMilestonesBlock = "
        <div class='card shadow-sm mt-4'>
            <div class='card-body'>
                <h2 class='h4 mb-3'>نقاط مراقبة الرصيد المخزن</h2>
                <p class='text-muted'>يوضح هذا الجدول كمية الخسائر التراكمية التي يحتفظ بها التطبيق عند طلبات متفرقة (كل 50 طلبًا أو عند تجاوز كل 5,000 عملة)، مع إبراز أكثر المستخدمين خسارة في تلك اللحظة.</p>
                <div class='table-responsive'>
                    <table class='table table-striped align-middle small'>
                        <thead class='table-light'>
                            <tr>
                                <th>رقم الطلب</th>
                                <th>الدور</th>
                                <th>الرصيد المخزن</th>
                                <th>التغير الأخير</th>
                                <th>مجموع خسائر اللاعبين</th>
                                <th>أكثر لاعب خسارة</th>
                                <th>صافي التطبيق حتى الآن</th>
                            </tr>
                        </thead>
                        <tbody>
                            {$storageMilestonesRows}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>";

        $storageStatsBlock = "
        <div class='card shadow-sm mt-4'>
            <div class='card-body'>
                <h2 class='h4 mb-4'>إحصائيات محفظة التطبيق</h2>
                <p class='text-muted'>يلخص هذا الجدول كيف تم تغذية محفظة الخسائر ومتى تم السحب أو التفريغ الكامل أثناء المحاكاة.</p>
                <div class='table-responsive'>
                    <table class='table table-bordered align-middle'>
                        <thead class='table-light'>
                            <tr>
                                <th>المؤشر</th>
                                <th>القيمة</th>
                                <th>ملاحظات</th>
                            </tr>
                        </thead>
                        <tbody>
                            {$storageStatsRows}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>";

        $historyTableBlock = "
        <div class='card shadow-sm mt-4'>
            <div class='card-body'>
                <h2 class='h4 mb-4'>سجل الطلبات حسب ترتيب اللعب</h2>
                <p class='text-muted'>يعرض هذا الجدول كل محاولة بحسب الترتيب الفعلي للاعبين خلال المحاكاة، ويمكنك استخدام نفس الفلتر بالأعلى لإظهار مستخدم واحد.</p>
                <div class='table-responsive'>
                    <table class='table table-hover align-middle small' data-filterable-table='true'>
                        <thead class='table-light'>
                            <tr>
                                <th>#</th>
                                <th>الدور</th>
                                <th>المستخدم</th>
                                <th>النتيجة</th>
                                <th>المضاعف</th>
                                <th>انحراف قبل</th>
                                <th>انحراف بعد</th>
                                <th>قيمة الرهان</th>
                                <th>قيمة الربح</th>
                                <th>خصم التطبيق</th>
                                <th>الرصيد قبل</th>
                                <th>الرصيد بعد</th>
                                <th>اتجاه التباين</th>
                                <th>مزاج الخدمة</th>
                            </tr>
                        </thead>
                        <tbody>
                            {$historyRows}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>";

        $html = "<!DOCTYPE html>
<html lang='ar' dir='rtl'>
<head>
    <meta charset='UTF-8'>
    <title>تقرير مواجهة الحظ الذكي</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { background:#f8f9fb; font-family:'Segoe UI', serif; }
        h1, h2 { font-weight:700; }
        .table thead th { white-space:nowrap; }
    </style>
</head>
<body>
    <div class='container py-5'>
        <h1 class='text-center mb-4'>تقرير مواجهة خدمة FairLuck Service 3</h1>
        <p class='text-center text-muted mb-4 mb-lg-5'>الهدية: {$gift->name} | السعر: " . number_format($gift->price) . " | إجمالي الأدوار: {$lastRound}</p>
        {$scenarioOverview}
        <div class='row'>
            {$summaryCards}
        </div>
        <div class='card shadow-sm'>
            <div class='card-body'>
                <h2 class='h4 mb-4'>ملخص المستخدمين</h2>
                <div class='table-responsive'>
                    <table class='table table-striped align-middle'>
                        <thead class='table-dark'>
                            <tr>
                                <th>المستخدم</th>
                                <th>المحاولات</th>
                                <th>مرات الفوز</th>
                                <th>مضاعفات ≥250</th>
                                <th>إجمالي المكاسب</th>
                                <th>إجمالي المدفوع</th>
                                <th>صافي اللاعب</th>
                                <th>خصم التطبيق</th>
                                <th>الرصيد النهائي</th>
                                <th>الدور الذي نفد فيه</th>
                                <th>RTP</th>
                                <th>أعلى مضاعف</th>
                            </tr>
                        </thead>
                        <tbody>
                            {$summaryTableRows}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        {$historyTableBlock}
        {$storageTableBlock}
        {$storageMilestonesBlock}
        {$storageStatsBlock}
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const filter = document.getElementById('playerFilter');
        if (!filter) {
            return;
        }

        const applyFilter = () => {
            const value = filter.value;
            document.querySelectorAll('[data-filterable-row]').forEach((row) => {
                const matches = value === 'all' || row.dataset.player === value;
                row.style.display = matches ? '' : 'none';
            });
        };

        filter.addEventListener('change', applyFilter);
        applyFilter();
    });
    </script>
</body>
</html>";

        $path = public_path('versus_lucky_report.html');
        File::put($path, $html);

        return $path;
    }
}
