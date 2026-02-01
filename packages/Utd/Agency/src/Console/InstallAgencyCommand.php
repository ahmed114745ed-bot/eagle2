<?php

namespace Utd\Agency\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class InstallAgencyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'agency:install 
                            {--force : Force installation even if already installed}
                            {--seed : Run seeders after installation}
                            {--no-menu : Skip adding admin menu items}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Agency package (creates tables, adds columns, sets up triggers, and adds admin menu)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('');
        $this->info('╔════════════════════════════════════════════════════════════╗');
        $this->info('║           Agency Package Installation                       ║');
        $this->info('╚════════════════════════════════════════════════════════════╝');
        $this->info('');

        // Check if already installed
        if (!$this->option('force') && $this->isInstalled()) {
            $this->warn('⚠️  Agency package appears to be already installed.');
            $this->warn('   Use --force to reinstall.');
            return Command::SUCCESS;
        }

        // Step 1: Run migrations
        $this->info('📦 Step 1/5: Running migrations...');
        $this->runMigrations();
        $this->info('   ✅ Migrations completed.');

        // Step 2: Publish config
        $this->info('📦 Step 2/5: Publishing configuration...');
        $this->publishConfig();
        $this->info('   ✅ Configuration published.');

        // Step 3: Clear caches
        $this->info('📦 Step 3/5: Clearing caches...');
        $this->clearCaches();
        $this->info('   ✅ Caches cleared.');

        // Step 4: Add admin menu
        if (!$this->option('no-menu')) {
            $this->info('📦 Step 4/5: Adding admin menu items...');
            $this->addAdminMenu();
        } else {
            $this->info('📦 Step 4/5: Skipping admin menu (use without --no-menu to add)');
        }

        // Step 5: Run seeders (optional)
        if ($this->option('seed')) {
            $this->info('📦 Step 5/5: Running seeders...');
            $this->runSeeders();
            $this->info('   ✅ Seeders completed.');
        } else {
            $this->info('📦 Step 5/5: Skipping seeders (use --seed to run)');
        }

        $this->info('');
        $this->info('╔════════════════════════════════════════════════════════════╗');
        $this->info('║           ✅ Installation Complete!                         ║');
        $this->info('╚════════════════════════════════════════════════════════════╝');
        $this->info('');
        $this->info('📝 Next steps:');
        $this->info('   1. Review config/agency-package.php');
        $this->info('   2. Enable the package in modules_statuses.json');
        $this->info('   3. Clear your application cache: php artisan cache:clear');
        $this->info('');

        return Command::SUCCESS;
    }

    /**
     * Check if the package is already installed.
     */
    protected function isInstalled(): bool
    {
        return Schema::hasTable('agencies') && Schema::hasColumn('users', 'agency_id');
    }

    /**
     * Run the package migrations.
     */
    protected function runMigrations(): void
    {
        // 0. Reset migration history for this package to ensure "all" run as requested
        $this->resetMigrationHistory();

        $this->call('migrate', [
            '--path' => 'packages/Utd/Agency/Database/Migrations',
            '--force' => true,
        ]);

        $this->fixSchema();
    }

    /**
     * Clear migration repository for package migrations to force them to be re-evaluated.
     */
    protected function resetMigrationHistory(): void
    {
        $migrationPath = base_path('packages/Utd/Agency/Database/Migrations');
        if (!is_dir($migrationPath)) {
            return;
        }

        $files = glob($migrationPath . '/*.php');
        $migrationsToReset = [];

        foreach ($files as $file) {
            $migrationsToReset[] = basename($file, '.php');
        }

        if (!empty($migrationsToReset)) {
            $this->info('🔄 Resetting migration history for ' . count($migrationsToReset) . ' files...');
            \Illuminate\Support\Facades\DB::table('migrations')->whereIn('migration', $migrationsToReset)->delete();
        }
    }
    /**
     * Publish the package configuration.
     */
    protected function publishConfig(): void
    {
        $this->call('vendor:publish', [
            '--tag' => 'agency-config',
            '--force' => $this->option('force'),
        ]);
    }

    /**
     * Clear application caches.
     */
    protected function clearCaches(): void
    {
        $this->callSilently('config:clear');
        $this->callSilently('cache:clear');
        $this->callSilently('route:clear');
    }

    /**
     * Run the package seeders.
     */
    protected function runSeeders(): void
    {
        // Check if seeder exists
        if (class_exists(\Utd\Agency\Database\Seeders\AgencySeeder::class)) {
            $this->call('db:seed', [
                '--class' => \Utd\Agency\Database\Seeders\AgencySeeder::class,
            ]);
        }
    }

    /**
     * Add admin menu items.
     */
    protected function addAdminMenu(): void
    {
        $seeder = new \Utd\Agency\Database\Seeders\AgencyMenuSeeder();
        $seeder->setCommand($this);
        $seeder->run();
    }
}
