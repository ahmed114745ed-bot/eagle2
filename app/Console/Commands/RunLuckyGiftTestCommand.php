<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class RunLuckyGiftTestCommand extends Command
{
    protected $signature = 'run:lucky-gift-test';
    protected $description = 'Run the SendLuckyGift2FeatureTest PHPUnit test';

    public function handle(): int
    {
        $this->info('🚀 Starting SendLuckyGift2FeatureTest...');

        $phpunitPath = base_path('vendor/bin/phpunit');
        $filter = 'SendLuckyGift2FeatureTest';

        $command = [
            'php',
            $phpunitPath,
            '--do-not-cache-result',
            '--filter',
            $filter,
        ];

        $process = new Process($command, base_path());
        $process->setTimeout(300);
        $process->run();

        $output = trim($process->getOutput() . $process->getErrorOutput());

        \Log::info('PHPUnit Test Run: SendLuckyGift2FeatureTest', [
            'command' => implode(' ', $command),
            'is_successful' => $process->isSuccessful(),
            'exit_code' => $process->getExitCode(),
            'output' => $output,
        ]);

        if ($process->isSuccessful()) {
            $this->info('✅ Test completed successfully!');
            return 1;
        } else {
            $this->error('❌ PHPUnit tests failed! Check storage/logs/laravel.log for details.');
            return 0;
        }
    }
}
