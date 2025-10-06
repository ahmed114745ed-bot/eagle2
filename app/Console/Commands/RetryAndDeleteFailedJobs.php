<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class RetryAndDeleteFailedJobs extends Command
{
    protected $signature = 'queue:retry-delete';
    protected $description = 'Retry all failed jobs and remove them after successful execution';

    public function handle()
    {
        $failedJobs = DB::table('failed_jobs')->get();

        foreach ($failedJobs as $job) {
            $this->info("Retrying failed job ID: {$job->id}");
            Artisan::call('queue:retry', ['id' => $job->id]);

            // Wait a moment to let it process
            sleep(1);

            // Check if the job is no longer in the failed_jobs table
            $stillFailed = DB::table('failed_jobs')->where('id', $job->id)->exists();

            if (! $stillFailed) {
                $this->info("✅ Job {$job->id} succeeded, removing from failed_jobs table.");
                DB::table('failed_jobs')->where('id', $job->id)->delete();
            } else {
                $this->warn("⚠️ Job {$job->id} failed again, keeping it in the table.");
            }
        }

        $this->info("All failed jobs processed.");
    }
}
