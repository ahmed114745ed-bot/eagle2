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
//         $days = DB::table('configs')->where( 'name','period_target')->value('value') ?? 20;
//         if ($days) {

//            $lastRun = DB::table('period_target')->orderBy('id', 'desc')->first();

            DB::table('period_target')->insert([
                'start_at' => Carbon::now()->startOfMonth()->addDays(20)->subDay()->setHour(18)->setMinute(0)->setSecond(0),
                'end_at' => Carbon::now()->addMonth()->startOfMonth()->addDays(19)->setHour(17)->setMinute(59)->setSecond(59),
                'created_at' => Carbon::now(),

            ]);

             $this->info("Cron job executed. Next execution will be in days.");
//         }
    }
}
