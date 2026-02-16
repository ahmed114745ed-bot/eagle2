<?php

namespace Utd\Family\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallFamilyCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'family:install {--force : تنفيذ بدون تأكيد}';

    /**
     * @var string
     */
    protected $description = 'تثبيت حزمة Family - نشر الإعدادات والتهجيرات';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 بدء تثبيت حزمة Family...');

        // 1. نشر ملف الإعدادات
        $this->info('📝 نشر ملف الإعدادات...');
        $this->call('vendor:publish', [
            '--provider' => 'Utd\Family\FamilyServiceProvider',
            '--tag' => 'config'
        ]);

        // 2. تشغيل التهجيرات
        $this->info('🗃️  تشغيل التهجيرات...');
        $this->call('migrate');

        // 3. إضافة الحزمة إلى composer.json إذا لم تكن موجودة
        $this->info('📦 تحديث composer.json...');
        $this->updateComposer();

        $this->newLine();
        $this->info('✅ تم تثبيت حزمة Family بنجاح!');
        
        return Command::SUCCESS;
    }

    protected function updateComposer()
    {
        $path = base_path('composer.json');
        if (!File::exists($path)) return;

        $composer = json_decode(File::get($path), true);
        
        if (!isset($composer['require']['utd/family'])) {
            $composer['require']['utd/family'] = '*';
            File::put($path, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->warn('⚠️  تمت إضافة الحزمة لـ composer.json. يرجى تنفيذ composer update');
        }
    }
}
