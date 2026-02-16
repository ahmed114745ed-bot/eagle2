<?php

namespace App\Console\Commands;

use App\Models\Gift;
use App\Models\User;
use App\Models\UserLuckProfile;
use App\Services\FairLuck\FairLuckService3;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SimulateLuckyGiftsVersus extends Command
{
    private const LABELS = ['A', 'B', 'C', 'D', 'E'];

    protected $signature = 'simulate:lucky-gifts-versus {gift_id?} {--rounds=30} {--balance=30000} {--player-count=3} {--price=} {--userA=} {--userB=} {--userC=} {--userD=} {--userE=}';
    protected $description = 'Simulate Service 3 among multiple users and generate an HTML timeline report';

    public function handle(FairLuckService3 $fairLuckService)
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

        for ($round = 1; $round <= $rounds; $round++) {
            foreach ($players as $label => $player) {
                $player->refresh();
                $currentBalance = (int) $player->di;

                if ($currentBalance < $gift->price) {
                    $this->markPlayerAsDrained($player, $stats[$label], $round);
                    continue;
                }

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

        $reportPath = $this->generateHtmlReport($gift, $stats, $history);

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

    private function generateHtmlReport(Gift $gift, array $stats, array $history): string
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
                                <p class='mb-1 small text-muted'>صافي اللاعب (المكاسب − المدفوع): <strong>" . number_format($netFlow) . "</strong></p>
                                <p class='mb-1 small text-muted'>تغير الرصيد (النهاية − البداية): <strong>" . number_format($balanceDelta) . "</strong></p>
                                <p class='mb-0 small text-muted'>معادلة التحقق: {$playerEquation}</p>
                            </div>
                        </div>
                    </div>
                </div>";
        }

        $summaryTableRows = '';
        foreach ($stats as $summary) {
            $netFlow = $summary['won'] - $summary['spent'];
            $drainRound = $summary['drained_at_round'] ? (string) $summary['drained_at_round'] : 'لم ينفد بعد';
            $maxMultiplierCell = $summary['max_multiplier'] > 0 ? number_format($summary['max_multiplier']) . 'x' : '—';
            $totalAvailable = $summary['initial'] + $summary['won'];
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

        $lastRound = $history ? $history[count($history) - 1]['round'] : 0;
        $totals['net'] = $totals['won'] - $totals['spent'];
        $globalRtp = $totals['spent'] > 0 ? ($totals['won'] / $totals['spent']) * 100 : 0;
        $scenarioEquation = number_format($totals['initial']) . ' + ' . number_format($totals['net']) . ' - ' . number_format($totals['house_cut']) . ' = ' . number_format($totals['final']);
        $playersCount = count($stats);

        $scenarioOverview = "
        <div class='alert alert-info shadow-sm mb-4'>
            <p class='mb-1 fw-bold'>هدف السيناريو</p>
            <p class='mb-3 mb-lg-4'>هذه المحاكاة تراقب طريقة توزيع Service 3 عندما يتنافس {$playersCount} لاعب/لاعبة على {$gift->name} بسعر رهان " . number_format($gift->price) . " عبر {$lastRound} دوراً، لقياس صافي كل لاعب مقابل مكسب التطبيق.</p>
            <div class='row text-center g-3'>
                <div class='col-6 col-lg-4'>
                    <small>إجمالي الرصيد الابتدائي</small>
                    <p class='h6 mb-0'>" . number_format($totals['initial']) . "</p>
                </div>
                <div class='col-6 col-lg-4'>
                    <small>إجمالي الرهانات</small>
                    <p class='h6 mb-0'>" . number_format($totals['available']) . "</p>
                </div>
                <div class='col-6 col-lg-4'>
                    <small>إجمالي المكاسب</small>
                    <p class='h6 mb-0 text-success'>" . number_format($totals['won']) . "</p>
                </div>
                
                <div class='col-6 col-lg-4'>
                    <small>صافي اللاعبين</small>
                    <p class='h6 mb-0'>" . number_format($totals['net']) . "</p>
                </div>
                <div class='col-6 col-lg-4'>
                    <small>خصم التطبيق</small>
                    <p class='h6 mb-0 text-warning'>" . number_format($totals['house_cut']) . "</p>
                </div>
                <div class='col-6 col-lg-4'>
                    <small>الرصيد النهائي المجمع</small>
                    <p class='h6 mb-0'>" . number_format($totals['final']) . "</p>
                </div>
            </div>
            <p class='mb-0 small text-muted'>RTP الكلي: " . number_format($globalRtp, 2) . "% | معادلة التحقق الجماعية: {$scenarioEquation}</p>
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
    </div>
</body>
</html>";

        $path = public_path('versus_lucky_report.html');
        File::put($path, $html);

        return $path;
    }
}
