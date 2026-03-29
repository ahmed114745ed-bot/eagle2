<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PrintDateTimeEveryMinute extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:print-datetime-every-minute';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Print the current date and time every minute';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
         $timezone = getTimezone();
        $dateTime = now()->format('Y-m-d H:i:s');
        $dateTimeZone = now($timezone)->format('Y-m-d H:i:s');
        $message = "Current Date and Time: {$dateTime} | Timezone: {$timezone} | Date and Time in Timezone: {$dateTimeZone}";
        
        // Print to console
        $this->info($message);
        
        // Log to file
        Log::info($message);
        
        return 0;
    }
}
