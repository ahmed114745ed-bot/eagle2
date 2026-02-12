<?php

namespace Utd\Reals\Console;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UninstallRealsCommand extends Command
{
    /**     *
     * @var string
     */
    protected $signature = 'reals:uninstall 
                            {--force : تنفيذ بدون تأكيد}
                            {--keep-data : الاحتفاظ بالبيانات (حذف الأعمدة المضافة فقط)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'إلغاء تثبيت حزمة Reals - حذف الجداول والأعمدة';

    protected array $tables = [
        'report_reals',
        'real_user_views',
        'real_user_comments',
        'real_user_likes',
        'real_categories',
        'reels_user_settings',
        'reals',
    ];

    protected array $sharedColumns = [
        'users' => ['reel_following_type'],
        'targets' => ['reel'],
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->warn('⚠️  تحذير: هذا الأمر سيحذف جميع بيانات Reals!');
        $this->newLine();

        if (! $this->option('force')) {
            if (! $this->confirm('هل أنت متأكد من رغبتك في إلغاء تثبيت حزمة Reals؟')) {
                $this->info('تم إلغاء العملية.');

                return Command::SUCCESS;
            }
        }

        $this->newLine();
        $this->info('🗑️  بدء إلغاء تثبيت حزمة Reals...');
        $this->newLine();

        // 1. حذف الأعمدة من الجداول المشتركة
        $this->info('📝 إزالة الأعمدة من الجداول المشتركة...');
        $this->removeSharedColumns();

        $this->newLine();

        // 2. حذف الجداول
        if (! $this->option('keep-data')) {
            $this->info('🗃️  حذف الجداول...');
            $this->dropTables();
        } else {
            $this->info('ℹ️  تم تخطي حذف الجداول (--keep-data)');
        }

        $this->newLine();

        // 3. حذف سجلات الـ migrations
        $this->info('📋 تنظيف سجلات الـ migrations...');
        $this->cleanupMigrationRecords();

        $this->newLine();

        $this->info('📁 حذف الملفات العامة...');
        $this->removePublishedAssets();

        $this->newLine();

        // 5. إزالة الحزمة من composer.json
        $this->info('📦 إزالة الحزمة من composer.json...');
        $this->removeFromComposer();

        $this->newLine();
        $this->info('✅ تم إلغاء تثبيت حزمة Reals بنجاح!');

        $this->newLine();
        $this->warn('📝 لإكمال الإزالة، نفذ الأوامر التالية:');
        $this->line('   1. composer dump-autoload');
        $this->line('   2. (اختياري) rm -rf packages/Utd/Reals');

        return Command::SUCCESS;
    }

    protected function removeSharedColumns(): void
    {
        foreach ($this->sharedColumns as $table => $columns) {
            if (! Schema::hasTable($table)) {
                $this->warn("  ⚠️  الجدول {$table} غير موجود");

                continue;
            }

            foreach ($columns as $column) {
                if (Schema::hasColumn($table, $column)) {
                    try {
                        Schema::table($table, function ($tableBlueprint) use ($column) {
                            $tableBlueprint->dropColumn($column);
                        });
                        $this->info("  ✅ تم حذف العمود {$column} من الجدول {$table}");
                    } catch (Exception $e) {
                        $this->error("  ❌ فشل في حذف العمود {$column}: ".$e->getMessage());
                    }
                } else {
                    $this->info("  ℹ️  العمود {$column} غير موجود في الجدول {$table}");
                }
            }
        }
    }

    /**
     * حذف الجداول
     */
    protected function dropTables(): void
    {
        // تعطيل فحص الـ foreign keys مؤقتاً
        Schema::disableForeignKeyConstraints();

        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                try {
                    Schema::dropIfExists($table);
                    $this->info("  ✅ تم حذف الجدول {$table}");
                } catch (Exception $e) {
                    $this->error("  ❌ فشل في حذف الجدول {$table}: ".$e->getMessage());
                }
            } else {
                $this->info("  ℹ️  الجدول {$table} غير موجود");
            }
        }

        // إعادة تفعيل فحص الـ foreign keys
        Schema::enableForeignKeyConstraints();
    }

    /**
     * تنظيف سجلات الـ migrations
     */
    protected function cleanupMigrationRecords(): void
    {
        $migrationPatterns = [
            '%create_reals_table%',
            '%create_real_categories_table%',
            '%create_real_user_likes_table%',
            '%create_real_user_comments_table%',
            '%create_real_user_views_table%',
            '%create_report_reals_table%',
            '%create_reels_user_settings_table%',
            '%add_reel_following_type%',
            '%edit_description_field_in_reals%',
        ];

        $deleted = 0;
        foreach ($migrationPatterns as $pattern) {
            $count = DB::table('migrations')
                ->where('migration', 'like', $pattern)
                ->delete();
            $deleted += $count;
        }

        $this->info("  ✅ تم حذف {$deleted} سجل من جدول migrations");
    }

    /**
     * حذف الـ assets المنشورة
     */
    protected function removePublishedAssets(): void
    {
        $assetsPath = public_path('modules/reals');

        // التحقق إذا كان symlink
        if (is_link($assetsPath)) {
            try {
                unlink($assetsPath);
                $this->info("  ✅ تم حذف الرابط الرمزي {$assetsPath}");
            } catch (Exception $e) {
                $this->error('  ❌ فشل في حذف الرابط الرمزي: '.$e->getMessage());
            }
        } elseif (is_dir($assetsPath)) {
            try {
                $this->deleteDirectory($assetsPath);
                $this->info("  ✅ تم حذف المجلد {$assetsPath}");
            } catch (Exception $e) {
                $this->error('  ❌ فشل في حذف المجلد: '.$e->getMessage());
            }
        } else {
            $this->info("  ℹ️  المجلد {$assetsPath} غير موجود");
        }
    }

    protected function deleteDirectory(string $dir): bool
    {
        if (! is_dir($dir)) {
            return false;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir.DIRECTORY_SEPARATOR.$file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }

        return rmdir($dir);
    }

    protected function removeFromComposer(): void
    {
        $composerPath = base_path('composer.json');

        if (! file_exists($composerPath)) {
            $this->error('  ❌ ملف composer.json غير موجود');

            return;
        }

        try {
            $composerContent = file_get_contents($composerPath);
            $composer = json_decode($composerContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error('  ❌ خطأ في قراءة composer.json: '.json_last_error_msg());

                return;
            }

            $modified = false;

            if (isset($composer['autoload']['psr-4']['Utd\\Reals\\'])) {
                unset($composer['autoload']['psr-4']['Utd\\Reals\\']);
                $modified = true;
                $this->info('  ✅ تم إزالة Utd\\Reals\\ من autoload.psr-4');
            }

            if (isset($composer['repositories']) && is_array($composer['repositories'])) {
                foreach ($composer['repositories'] as $key => $repo) {
                    if (isset($repo['url']) && mb_strpos($repo['url'], 'packages/Utd/Reals') !== false) {
                        unset($composer['repositories'][$key]);
                        $composer['repositories'] = array_values($composer['repositories']);
                        $modified = true;
                        $this->info('  ✅ تم إزالة repository الحزمة');
                    }
                }
            }

            if ($modified) {
                $newContent = json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                file_put_contents($composerPath, $newContent."\n");
                $this->info('  ✅ تم تحديث composer.json بنجاح');
            } else {
                $this->info('  ℹ️  الحزمة غير موجودة في composer.json');
            }

        } catch (Exception $e) {
            $this->error('  ❌ فشل في تحديث composer.json: '.$e->getMessage());
        }
    }
}
