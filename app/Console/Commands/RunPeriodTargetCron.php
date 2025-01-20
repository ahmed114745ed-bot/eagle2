<?php

namespace App\Console\Commands;

use App\Models\Config;
use App\Models\PeriodTarget;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RunPeriodTargetCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'period-target:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
         $days = DB::table('configs')->where( 'name','period_target')->value('value'); 
        //  dd($lastRun);
         if ($days) {
          
            $lastRun = DB::table('period_target')->orderBy('id', 'desc')->first();

            DB::table('period_target')->insert([
                'start_at' => $lastRun ? $lastRun->end_at : now(), 
                'end_at' => now()->addDays($days), 
            ]);
 
             $this->info("Cron job executed. Next execution will be in {$days} days.");
         }
    }
}
