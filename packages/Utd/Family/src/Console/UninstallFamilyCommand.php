<?php

namespace Utd\Family\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class UninstallFamilyCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'family:uninstall 
                            {--force : تنفيذ بدون تأكيد}';

    /**
     * @var string
     */
    protected $description = 'إلغاء تثبيت حزمة Family دون حذف أي بيانات من قاعدة البيانات';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->warn('⚠️  تحذير: هذا الأمر سيعطل حزمة Family دون أي حذف للبيانات.');
        $this->newLine();

        if (! $this->option('force')) {
            if (! $this->confirm('هل أنت متأكد من رغبتك في إلغاء تثبيت حزمة Family؟')) {
                $this->info('تم إلغاء العملية.');
                return Command::SUCCESS;
            }
        }

        $this->info('🗑️  بدء إلغاء التثبيت...');
        $this->line('ℹ️ لن يتم حذف أي جداول أو أعمدة. قم بإزالة البيانات يدويًا إذا لزم الأمر.');

        $this->removeFromComposer();
        $this->removeAutoloadEntries();
        $this->removeServiceProviderReference();

        $this->runComposerDumpAutoload();
        $this->clearLaravelCaches();

        $this->newLine();
        $this->info('✅ تم إلغاء تثبيت حزمة Family بنجاح!');
        $this->line('🧹 تم تنفيذ composer dump-autoload وتهيئة الكاش تلقائياً.');

        return Command::SUCCESS;
    }

   

    protected function removeFromComposer()
    {
        $path = base_path('composer.json');
        if (! File::exists($path)) {
            return;
        }

        $composer = json_decode(File::get($path), true);
        $modified = false;

        if (isset($composer['require']['utd/family'])) {
            unset($composer['require']['utd/family']);
            $modified = true;
            $this->line('🔧 تم حذف utd/family من قسم require.');
        }

        if (isset($composer['repositories']) && is_array($composer['repositories'])) {
            $composer['repositories'] = array_values(array_filter($composer['repositories'], function ($repo) {
                return ! (isset($repo['url']) && str_contains($repo['url'], 'packages/Utd/Family'));
            }));
            $modified = true;
        }

        if ($modified) {
            File::put($path, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->info('📦 تم تحديث composer.json.');
        } else {
            $this->line('ℹ️ لا توجد إدخالات للحزمة داخل require أو repositories.');
        }
    }

    protected function removeAutoloadEntries(): void
    {
        $path = base_path('composer.json');
        if (! File::exists($path)) {
            return;
        }

        $composer = json_decode(File::get($path), true);
        $modified = false;

        if (isset($composer['autoload']['psr-4']['Utd\\Family\\'])) {
            unset($composer['autoload']['psr-4']['Utd\\Family\\']);
            $modified = true;
            $this->line('🔧 تم حذف Utd\\Family\\ من autoload.psr-4.');
        }

        if (! empty($composer['autoload']['files'])) {
            $before = count($composer['autoload']['files']);
            $composer['autoload']['files'] = array_values(array_filter(
                $composer['autoload']['files'],
                fn ($file) => $file !== 'packages/Utd/Family/src/Support/helpers.php'
            ));

            if ($before !== count($composer['autoload']['files'])) {
                $modified = true;
                $this->line('🔧 تم حذف ملف helpers الخاص بالحزمة من autoload.files.');
            }
        }

        if ($modified) {
            File::put($path, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->info('📚 تم تحديث قسم autoload في composer.json.');
        } else {
            $this->line('ℹ️ لا توجد إدخالات تلقائية يجب حذفها.');
        }
    }

    protected function removeServiceProviderReference(): void
    {
        $path = config_path('app.php');
        if (! File::exists($path)) {
            $this->warn('⚠️ ملف config/app.php غير موجود، تخطي إزالة مزود الخدمة.');
            return;
        }

        $content = File::get($path);
        $needleVariants = [
            "\\Utd\\Family\\FamilyServiceProvider::class,\n",
            "\\Utd\\Family\\FamilyServiceProvider::class,\r\n",
            "    \\Utd\\Family\\FamilyServiceProvider::class,\n",
            "    \\Utd\\Family\\FamilyServiceProvider::class,\r\n",
            "Utd\\Family\\FamilyServiceProvider::class,\n",
            "Utd\\Family\\FamilyServiceProvider::class,\r\n",
        ];

        $replaced = false;
        foreach ($needleVariants as $needle) {
            if (str_contains($content, $needle)) {
                $content = str_replace($needle, '', $content);
                $replaced = true;
            }
        }

        if ($replaced) {
            File::put($path, $content);
            $this->info('⚙️ تم إزالة FamilyServiceProvider من config/app.php.');
        } else {
            $this->line('ℹ️ لم يتم العثور على FamilyServiceProvider داخل config/app.php.');
        }
    }

    protected function runComposerDumpAutoload(): void
    {
        $this->info('🔁 تشغيل composer dump-autoload...');

        $process = proc_open(
            'composer dump-autoload',
            [
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            base_path()
        );

        if (! is_resource($process)) {
            $this->warn('⚠️ تعذر تشغيل composer dump-autoload تلقائياً. نفذه يدوياً.');
            return;
        }

        $output = stream_get_contents($pipes[1]);
        $error = stream_get_contents($pipes[2]);

        foreach ($pipes as $pipe) {
            fclose($pipe);
        }

        $exitCode = proc_close($process);

        if ($exitCode === 0) {
            $this->line('   ✅ composer dump-autoload تم بنجاح.');
        } else {
            $this->warn('⚠️ composer dump-autoload فشل. الرجاء تنفيذ الأمر يدوياً.');
            if ($error) {
                $this->warn($error);
            }
        }
    }

    protected function clearLaravelCaches(): void
    {
        $this->info('🧽 مسح كاش Laravel...');
        $this->callSilent('config:clear');
        $this->callSilent('cache:clear');
        $this->callSilent('route:clear');
        $this->callSilent('view:clear');
        $this->line('   ✅ تم مسح الكاش.');
    }

}
