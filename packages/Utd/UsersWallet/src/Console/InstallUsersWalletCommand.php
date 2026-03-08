<?php

namespace Utd\UsersWallet\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class InstallUsersWalletCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users-wallet:install 
                            {--force : Force installation even if already installed}
                            {--seed : Run seeders after installation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the UsersWallet package (creates tables and publishes config)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('📦 Starting UsersWallet Package Installation...');

        // Step 1: Run migrations
        $this->info('📦 Step 1/3: Running migrations...');
        $this->call('migrate', [
            '--path' => 'packages/Utd/UsersWallet/Database/Migrations',
            '--force' => true,
        ]);
        $this->info('   ✅ Migrations completed.');

        // Step 2: Publish config
        $this->info('📦 Step 2/3: Publishing configuration...');
        $this->call('vendor:publish', [
            '--tag' => 'userswallet-config',
            '--force' => $this->option('force'),
        ]);
        $this->info('   ✅ Configuration published.');

        // Step 3: Clear caches
        $this->info('📦 Step 3/3: Clearing caches...');
        $this->callSilently('config:clear');
        $this->callSilently('cache:clear');
        $this->info('   ✅ Caches cleared.');

        $this->info('✅ UsersWallet Installation Complete!');

        return Command::SUCCESS;
    }
}
