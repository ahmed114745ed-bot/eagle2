<?php

namespace Utd\UsersWallet\Console;

use Illuminate\Console\Command;

class UninstallUsersWalletCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users-wallet:uninstall';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uninstall the UsersWallet package (clears caches and warns about tables)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->warn('🛑 Uninstalling UsersWallet Package...');

        if ($this->confirm('Do you want to clear the application cache?', true)) {
            $this->callSilently('config:clear');
            $this->callSilently('cache:clear');
            $this->info('   ✅ Caches cleared.');
        }

        $this->info('✅ UsersWallet Uninstalled (Tables were preserved).');

        return Command::SUCCESS;
    }
}
