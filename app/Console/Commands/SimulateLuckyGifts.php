<?php

namespace App\Console\Commands;

use App\Models\Gift;
use App\Models\User;
use App\Services\FairLuck\FairLuckService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SimulateLuckyGifts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simulate:lucky-gifts {trials=100} {gift_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulate sending lucky gifts and generate a report on wins, losses, and deviation analysis';

    /**
     * Execute the console command.
     *
     * @param FairLuckService $fairLuckService
     * @return int
     */
    public function handle(FairLuckService $fairLuckService)
    {
        $trials = (int) $this->argument('trials');
        
        // Find a lucky gift if ID not provided
        $giftQuery = Gift::where('type', 6)->where('enable', 1);
        $giftId = $this->argument('gift_id') ?? $giftQuery->first()?->id;

        if (!$giftId) {
            $this->error('No lucky gift found in database. Please ensure gifts with type=6 exist.');
            return 1;
        }

        $gift = Gift::with('luckyGift')->find($giftId);
        if (!$gift) {
            $this->error("Gift with ID {$giftId} not found.");
            return 1;
        }

        $this->info("🚀 Starting simulation for user with gift: {$gift->name} (Price: {$gift->price})");
        $this->info("📊 Number of trials: {$trials}");

        // Create a temporary user via factory
        $user = User::factory()->create([
            'di' => $gift->price * $trials * 2, // Give enough coins for the simulation
            'name' => 'SimulationUser_' . now()->timestamp,
        ]);

        $this->info("👤 Created temporary user: {$user->name} (ID: {$user->id})");

        $stats = [
            'total_bets' => 0,
            'total_wins' => 0,
            'total_profit' => 0,
            'wins_count' => 0,
            'losses_count' => 0,
            'deviations' => [],
            'multipliers' => [],
            'history' => [],
        ];

        $bar = $this->output->createProgressBar($trials);
        $bar->start();

        for ($i = 0; $i < $trials; $i++) {
            // Use FairLuckService to process the bet
            $result = $fairLuckService->processBet($user, $gift, $gift->price);
            
            $stats['total_bets'] += $gift->price;
            $stats['total_profit'] += $result->profitAmount;
            $stats['deviations'][] = (float) $result->newDeviation;
            
            $historyEntry = [
                'step' => $i + 1,
                'result' => $result->isWinner ? 'WIN ✅' : 'LOSS ❌',
                'multiplier' => $result->isWinner ? $result->multiplier . 'x' : '-',
                'profit' => number_format($result->profitAmount, 2),
                'deviation' => number_format($result->newDeviation, 4),
            ];

            if ($result->isWinner) {
                $stats['wins_count']++;
                // In FairLuckService: profitAmount = (multiplier * bet) - bet
                // So Total Win = profitAmount + bet
                $stats['total_wins'] += ($result->profitAmount + $gift->price);
                $stats['multipliers'][] = $result->multiplier;
                $historyEntry['result'] = '<fg=green>WIN ✅</>';
            } else {
                $stats['losses_count']++;
                $historyEntry['result'] = '<fg=red>LOSS ❌</>';
            }

            $stats['history'][] = $historyEntry;
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->displayReport($stats, $trials, $gift);

        // Cleanup: Removing the test user and their transactions
        // Note: FairLuckService creates FairLuckTransaction records.
        $this->comment("Cleaning up temporary data...");
        
        DB::table('fair_luck_transactions')->where('user_id', $user->id)->delete();
        DB::table('user_luck_profiles')->where('user_id', $user->id)->delete();
        $user->profile()->delete(); // Depends on relationship being set up correctly
        $user->delete();

        $this->info("✅ Simulation complete and temporary user deleted.");

        return 0;
    }

    /**
     * Display the simulation report.
     *
     * @param array $stats
     * @param int $trials
     * @param Gift $gift
     */
    private function displayReport($stats, $trials, $gift)
    {
        $this->info("🏆 --- All Wins Log ({$stats['wins_count']} wins) ---");
        $winHeaders = ['# Trial', 'Multiplier', 'Profit', 'Deviation After Win'];
        $winRows = [];
        
        foreach ($stats['history'] as $entry) {
            if (str_contains($entry['result'], 'WIN')) {
                $winRows[] = [
                    $entry['step'],
                    $entry['multiplier'],
                    $entry['profit'],
                    $entry['deviation']
                ];
            }
        }
        
        if (count($winRows) > 0) {
            $this->table($winHeaders, $winRows);
        } else {
            $this->warn("No wins recorded in this simulation.");
        }

        $this->newLine();
        $this->info("📊 --- Summary Report ---");
        $rtp = $stats['total_bets'] > 0 ? ($stats['total_wins'] / $stats['total_bets']) * 100 : 0;
        $avgMultiplier = count($stats['multipliers']) > 0 ? array_sum($stats['multipliers']) / count($stats['multipliers']) : 0;
        $finalDeviation = end($stats['deviations']);
        
        $headers = ['Metric', 'Value'];
        $data = [
            ['Total Trials', $trials],
            ['Gift Name', $gift->name],
            ['Gift Price', $gift->price . ' coins'],
            ['Total Bets Value', number_format($stats['total_bets'], 2) . ' coins'],
            ['Total Returns', number_format($stats['total_wins'], 2) . ' coins'],
            ['Net Profit/Loss', number_format($stats['total_profit'], 2) . ' coins'],
            ['Win Count', $stats['wins_count']],
            ['Loss Count', $stats['losses_count']],
            ['Win Rate', number_format(($stats['wins_count'] / $trials) * 100, 2) . '%'],
            ['RTP (Return to Player)', number_format($rtp, 2) . '%'],
            ['Average Win Multiplier', number_format($avgMultiplier, 2) . 'x'],
            ['Final Deviation', number_format($finalDeviation, 4)],
        ];

        $this->table($headers, $data);

        $this->info("\n📈 --- Deviation Analysis ---");
        
        // Deviation typically ranges from -1 to 1 (or more)
        // Positive means user is winning more than expected RTP
        // Negative means user is winning less than expected RTP
        
        if ($finalDeviation > 0.05) {
            $this->warn("Analysis: The user is in DEFICIT (lost MORE than target loss).");
            $this->line("The system deviation is positive (".number_format($finalDeviation, 4).").");
            $this->line("Impact: The Algorithm will now INCREASE the win probability to help the user recover.");
        } elseif ($finalDeviation < -0.05) {
            $this->warn("Analysis: The user is in SURPLUS (winning MORE than target loss).");
            $this->line("The system deviation is negative (".number_format($finalDeviation, 4).").");
            $this->line("Impact: The Algorithm will now REDUCE the win probability to bring the user back to target RTP.");
        } else {
            $this->info("Analysis: The user is near the TARGET deviation.");
            $this->line("Performance is closely aligned with the configured RTP.");
        }
        
        $this->newLine();
    }
}
