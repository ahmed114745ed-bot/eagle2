<?php

namespace App\Console\Commands;

use App\Services\FairLuck\HighMultiplierLedger;
use Illuminate\Console\Command;

class FairLuckLeaderboardCommand extends Command
{
    protected $signature = 'fairluck:leaders {--limit=10 : Number of users to show}';

    protected $description = 'Display FairLuck high multiplier scores and pool status';

    public function __construct(private HighMultiplierLedger $ledger)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $leaders = $this->ledger->leaderboard($limit);
        $pool = $this->ledger->getPool();
        $tiers = config('fairluck.high_multiplier.tiers', []);

        if (empty($leaders)) {
            $this->warn('No ranked users yet.');
        } else {
            $rows = [];
            foreach ($leaders as $index => $entry) {
                $rows[] = [
                    '#' . ($index + 1),
                    $entry['user_id'],
                    number_format($entry['score'], 2),
                ];
            }

            $this->table(['Rank', 'User ID', 'Score'], $rows);
        }

        $this->line(sprintf('Pool Balance: %s', number_format($pool, 2)));
        $this->line('Tier Thresholds:' );
        foreach ($tiers as $multiplier => $threshold) {
            $this->line(sprintf('  x%s => score %s', $multiplier, number_format($threshold, 2)));
        }

        return self::SUCCESS;
    }
}
