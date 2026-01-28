<?php

namespace Utd\Agency\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UninstallAgencyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'agency:uninstall 
                            {--force : Force uninstall without confirmation}
                            {--keep-data : Keep database data (only remove code references)}
                            {--keep-menu : Keep admin menu items}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uninstall the Agency package (removes tables, columns, triggers, and admin menu)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('');
        $this->warn('╔════════════════════════════════════════════════════════════╗');
        $this->warn('║           Agency Package Uninstallation                     ║');
        $this->warn('╚════════════════════════════════════════════════════════════╝');
        $this->info('');

        // Confirm uninstallation
        if (!$this->option('force')) {
            $this->warn('⚠️  WARNING: This will remove all Agency package data!');
            $this->warn('   This action cannot be undone.');
            $this->info('');
            
            if (!$this->confirm('Are you sure you want to uninstall the Agency package?')) {
                $this->info('Uninstallation cancelled.');
                return Command::SUCCESS;
            }
        }

        if (!$this->option('keep-data')) {
            // Step 1: Remove triggers
            $this->info('🗑️  Step 1/4: Removing database triggers...');
            $this->removeTriggers();
            $this->info('   ✅ Triggers removed.');

            // Step 2: Remove columns from related tables
            $this->info('🗑️  Step 2/4: Removing columns from related tables...');
            $this->removeColumns();
            $this->info('   ✅ Columns removed.');

            // Step 3: Remove agency tables
            $this->info('🗑️  Step 3/4: Removing agency tables...');
            $this->removeTables();
            $this->info('   ✅ Tables removed.');
        } else {
            $this->info('⏭️  Skipping data removal (--keep-data option)');
        }

        // Step 4: Clear caches
        $this->info('🗑️  Step 4/5: Clearing caches...');
        $this->clearCaches();
        $this->info('   ✅ Caches cleared.');

        // Step 5: Remove admin menu
        if (!$this->option('keep-menu')) {
            $this->info('🗑️  Step 5/5: Removing admin menu items...');
            $this->removeAdminMenu();
            $this->info('   ✅ Admin menu removed.');
        } else {
            $this->info('⏭️  Skipping admin menu removal (--keep-menu option)');
        };

        $this->info('');
        $this->info('╔════════════════════════════════════════════════════════════╗');
        $this->info('║           ✅ Uninstallation Complete!                       ║');
        $this->info('╚════════════════════════════════════════════════════════════╝');
        $this->info('');
        $this->info('📝 Don\'t forget to:');
        $this->info('   1. Remove "Utd\\\\Agency" from composer.json autoload');
        $this->info('   2. Remove AgencyServiceProvider from config/app.php');
        $this->info('   3. Run: composer dump-autoload');
        $this->info('');

        return Command::SUCCESS;
    }

    /**
     * Remove database triggers.
     */
    protected function removeTriggers(): void
    {
        $triggers = [
            'update_agrncy_target',
            'update_agrncy_target1',
            'update_agrncy_target_after_change_agency',
        ];

        foreach ($triggers as $trigger) {
            try {
                DB::unprepared("DROP TRIGGER IF EXISTS {$trigger}");
            } catch (\Exception $e) {
                $this->warn("   ⚠️  Could not drop trigger {$trigger}: " . $e->getMessage());
            }
        }
    }

    /**
     * Remove agency columns from related tables.
     */
    protected function removeColumns(): void
    {
        $columns = [
            'users' => ['agency_id', 'type_user', 'is_manger', 'is_host'],
            'gift_logs' => ['agency_id'],
            'charges' => ['agency_id'],
            'user_sallaries' => ['user_agency_id'],
            'usd_transfers' => ['agency_id'],
        ];

        foreach ($columns as $table => $cols) {
            if (Schema::hasTable($table)) {
                foreach ($cols as $column) {
                    if (Schema::hasColumn($table, $column)) {
                        try {
                            Schema::table($table, function ($t) use ($column) {
                                $t->dropColumn($column);
                            });
                        } catch (\Exception $e) {
                            $this->warn("   ⚠️  Could not drop {$table}.{$column}: " . $e->getMessage());
                        }
                    }
                }
            }
        }
    }

    /**
     * Remove agency tables.
     */
    protected function removeTables(): void
    {
        $tables = [
            'bd_agency_host_sallaries',
            'percentage_agency_manger',
            'change_agency_mangers',
            'agency_manger_app_dash',
            'agency_manger_pulling_out',
            'agency_manger_deleteds',
            'targets',
            'histories',
            'agency_user_jobs',
            'agency_packs',
            'agency_sallaries',
            'user_sallaries',
            'user_target',
            'users_joined_agencies',
            'agency_join_requests',
            'agencies',
        ];

        Schema::disableForeignKeyConstraints();
        
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                try {
                    Schema::drop($table);
                } catch (\Exception $e) {
                    $this->warn("   ⚠️  Could not drop table {$table}: " . $e->getMessage());
                }
            }
        }
        
        Schema::enableForeignKeyConstraints();
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
     * Remove admin menu items.
     */
    protected function removeAdminMenu(): void
    {
        // Find the main Agency System menu
        $agencySystemMenu = DB::table('admin_menu')
            ->where('title', 'Agency System')
            ->where(function ($query) {
                $query->whereNull('parent_id')
                    ->orWhere('parent_id', 0);
            })
            ->first();

        if (!$agencySystemMenu) {
            $this->info('   ℹ️  No Agency menu found to remove.');
            return;
        }

        // Get all child menu IDs (recursive)
        $menuIds = [$agencySystemMenu->id];
        $this->getChildMenuIds($agencySystemMenu->id, $menuIds);

        // Delete all agency menu items
        DB::table('admin_menu')->whereIn('id', $menuIds)->delete();
    }

    /**
     * Get all child menu IDs recursively.
     */
    protected function getChildMenuIds(int $parentId, array &$ids): void
    {
        $children = DB::table('admin_menu')
            ->where('parent_id', $parentId)
            ->pluck('id')
            ->toArray();

        foreach ($children as $childId) {
            $ids[] = $childId;
            $this->getChildMenuIds($childId, $ids);
        }
    }
}
