<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixMigrationsCommand extends Command
{
    protected $signature = 'migrations:fix-create {--dry-run : Show changes without applying}';
    protected $description = 'Replace Schema::create with Schema::createIfNotExists in all migrations';

    public function handle()
    {
        $paths = [
            database_path('migrations'),
            base_path('Modules/*/Database/Migrations'),
            base_path('packages/*/Database/Migrations'),
            base_path('packages/*/*/Database/Migrations'),
        ];

        $count = 0;
        $dryRun = $this->option('dry-run');

        foreach ($paths as $pathPattern) {
            $directories = glob($pathPattern, GLOB_ONLYDIR) ?: [$pathPattern];
            
            foreach ($directories as $directory) {
                if (!is_dir($directory)) continue;
                
                $files = File::glob($directory . '/*.php');
                
                foreach ($files as $file) {
                    $content = File::get($file);
                    
                    // Skip if already using createIfNotExists or has hasTable check
                    if (str_contains($content, 'createIfNotExists') || str_contains($content, 'hasTable')) {
                        continue;
                    }
                    
                    // Replace Schema::create with Schema::createIfNotExists
                    $newContent = preg_replace(
                        '/Schema::create\s*\(\s*([\'"][^\'"]+[\'"])\s*,/',
                        'Schema::createIfNotExists($1,',
                        $content
                    );
                    
                    if ($content !== $newContent) {
                        $count++;
                        $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file);
                        
                        if ($dryRun) {
                            $this->info("Would update: {$relativePath}");
                        } else {
                            File::put($file, $newContent);
                            $this->info("Updated: {$relativePath}");
                        }
                    }
                }
            }
        }

        if ($dryRun) {
            $this->warn("Dry run complete. {$count} files would be updated.");
        } else {
            $this->info("Done! Updated {$count} migration files.");
        }

        return 0;
    }
}
