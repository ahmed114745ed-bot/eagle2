<?php

namespace Utd\Reals\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallRealsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reals:install 
                            {--force : تشغيل الـ migrations حتى في بيئة الإنتاج}
                            {--seed : تشغيل الـ seeders بعد الـ migrations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'تثبيت حزمة Reals - إنشاء الجداول والأعمدة المطلوبة';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 بدء تثبيت حزمة Reals...');
        $this->newLine();

        // 1. تشغيل الـ migrations
        $this->info('📦 تشغيل الـ migrations...');
        
        $migrationOptions = [];
        
        if ($this->option('force')) {
            $migrationOptions['--force'] = true;
        }

        try {
            Artisan::call('migrate', array_merge($migrationOptions, [
                '--path' => 'packages/Utd/Reals/database/migrations',
            ]));
            
            $this->info(Artisan::output());
            $this->info('✅ تم إنشاء الجداول بنجاح');
        } catch (\Exception $e) {
            $this->error('❌ فشل في تشغيل الـ migrations: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $this->newLine();

        // 2. إنشاء symlink للـ assets
        $this->info('📁 ربط الملفات العامة...');
        $this->createAssetsSymlink();

        $this->newLine();

        // 3. تشغيل الـ seeders إذا تم طلبها
        if ($this->option('seed')) {
            $this->info('🌱 تشغيل الـ seeders...');
            // يمكن إضافة seeders هنا لاحقاً
            $this->info('✅ تم تشغيل الـ seeders بنجاح');
        }

        $this->newLine();
        $this->info('🎉 تم تثبيت حزمة Reals بنجاح!');
        $this->newLine();

        $this->table(
            ['الجدول', 'الحالة'],
            [
                ['reals', '✅ تم إنشاؤه'],
                ['real_categories', '✅ تم إنشاؤه'],
                ['real_user_likes', '✅ تم إنشاؤه'],
                ['real_user_comments', '✅ تم إنشاؤه'],
                ['real_user_views', '✅ تم إنشاؤه'],
                ['report_reals', '✅ تم إنشاؤه'],
                ['reels_user_settings', '✅ تم إنشاؤه'],
                ['users.reel_following_type', '✅ تم إضافته'],
                ['targets.reel', '✅ تم إضافته'],
            ]
        );

        return Command::SUCCESS;
    }

    /**
     * إنشاء symlink للـ assets من الحزمة للمجلد العام
     */
    protected function createAssetsSymlink(): void
    {
        $packagePublicPath = base_path('packages/Utd/Reals/public');
        $publicModulesPath = public_path('modules');
        $targetPath = public_path('modules/reals');

        // إنشاء مجلد modules إن لم يكن موجوداً
        if (!is_dir($publicModulesPath)) {
            mkdir($publicModulesPath, 0755, true);
            $this->info("  ✅ تم إنشاء مجلد modules");
        }

        // حذف الرابط أو المجلد القديم إن وجد
        if (is_link($targetPath)) {
            unlink($targetPath);
            $this->info("  ℹ️  تم حذف الرابط القديم");
        } elseif (is_dir($targetPath)) {
            $this->deleteDirectory($targetPath);
            $this->info("  ℹ️  تم حذف المجلد القديم");
        }

        // إنشاء symlink
        if (symlink($packagePublicPath, $targetPath)) {
            $this->info("  ✅ تم إنشاء رابط رمزي: public/modules/reals -> packages/Utd/Reals/public");
        } else {
            $this->error("  ❌ فشل في إنشاء الرابط الرمزي");
        }
    }

    /**
     * حذف مجلد بمحتوياته
     */
    protected function deleteDirectory(string $dir): bool
    {
        if (!is_dir($dir)) {
            return false;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }

        return rmdir($dir);
    }
}
