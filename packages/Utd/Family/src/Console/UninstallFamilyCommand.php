<?php

namespace Utd\Family\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class UninstallFamilyCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'family:uninstall 
                            {--force : تنفيذ بدون تأكيد}
                            {--keep-data : الاحتفاظ بالبيانات (حذف الأعمدة المضافة فقط)}';

    /**
     * @var string
     */
    protected $description = 'إلغاء تثبيت حزمة Family - حذف الجداول والأعمدة';

    protected array $tables = [
        'family_user',
        'family_views',
        'family_levels',
        'families_ranks',
        'families',
    ];

    protected array $sharedColumns = [
        'users' => ['family_id'],
        'user_target' => ['family_id'],
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->warn('⚠️  تحذير: هذا الأمر سيحذف بيانات حزمة Family!');
        $this->newLine();

        if (!$this->option('force')) {
            if (!$this->confirm('هل أنت متأكد من رغبتك في إلغاء تثبيت حزمة Family؟')) {
                $this->info('تم إلغاء العملية.');
                return Command::SUCCESS;
            }
        }

        $this->info('🗑️  بدء إلغاء التثبيت...');

        // 1. إزالة الأعمدة من الجداول المشتركة
        $this->removeSharedColumns();

        // 2. حذف الجداول
        if (!$this->option('keep-data')) {
            $this->dropTables();
        }

        // 3. تنظيف سجلات migrations
        $this->cleanupMigrationRecords();

        // 4. إزالة من composer
        $this->removeFromComposer();

        $this->newLine();
        $this->info('✅ تم إلغاء تثبيت حزمة Family بنجاح!');
        $this->warn('📝 نفذ composer dump-autoload لإكمال الإزالة.');

        return Command::SUCCESS;
    }

    protected function removeSharedColumns()
    {
        foreach ($this->sharedColumns as $table => $columns) {
            if (!Schema::hasTable($table)) continue;

            Schema::table($table, function ($blueprint) use ($table, $columns) {
                foreach ($columns as $column) {
                    if (Schema::hasColumn($table, $column)) {
                        $blueprint->dropColumn($column);
                    }
                }
            });
        }
    }

    protected function dropTables()
    {
        Schema::disableForeignKeyConstraints();
        foreach ($this->tables as $table) {
            Schema::dropIfExists($table);
        }
        Schema::enableForeignKeyConstraints();
    }

    protected function cleanupMigrationRecords()
    {
        DB::table('migrations')
            ->where('migration', 'like', '%_family_%')
            ->orWhere('migration', 'like', '%_families_%')
            ->delete();
    }

    protected function removeFromComposer()
    {
        $path = base_path('composer.json');
        if (!File::exists($path)) return;

        $composer = json_decode(File::get($path), true);
        if (isset($composer['require']['utd/family'])) {
            unset($composer['require']['utd/family']);
            File::put($path, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
    }
}
