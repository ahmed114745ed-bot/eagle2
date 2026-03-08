<?php

namespace App\Console\Commands;

use App\Models\FairLuckWallet;
use Illuminate\Console\Command;

class SyncFairLuckWallets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fairluck:sync-wallets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync FairLuck wallet balances from Redis to Database for durability';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $walletTypes = [
            FairLuckWallet::TYPE_GLOBAL_VAULT,
            FairLuckWallet::TYPE_JACKPOT_WALLET,
            FairLuckWallet::TYPE_MEDIUM_WALLET
        ];

        foreach ($walletTypes as $type) {
            $this->info("Syncing {$type}...");
            FairLuckWallet::syncToDatabase($type);
        }

        $this->info("FairLuck wallets synced successfully at " . now()->toDateTimeString());
    }
}
