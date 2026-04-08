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
    protected $description = 'Sync FairLuck wallet balance from Redis to Database for durability (V7: single wallet)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // V7: Single unified wallet — only sync global_vault
        $this->info("Syncing global_vault...");
        FairLuckWallet::syncToDatabase(FairLuckWallet::TYPE_GLOBAL_VAULT);

        $this->info("FairLuck wallet synced successfully at " . now()->toDateTimeString());
    }
}
