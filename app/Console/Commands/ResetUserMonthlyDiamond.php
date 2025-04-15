<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Traits\Salaries\UserSalaryTrait;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResetUserMonthlyDiamond extends Command
{
    use UserSalaryTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:reset-monthly-diamond';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'reset monthly diamond after 30 day';

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
     * @return mixed
     */
    public function handle()
    {

        if (now()->day == 1){
            $carbon = now()->subDay();
            $this->updateUserSalary(month: $carbon->month, year: $carbon->year);
        }else{
            $this->updateUserSalary();
        }



        DB::statement("
            UPDATE users
            SET monthly_diamond_received = 0
            WHERE agency_id != 0
        ");

//        try {
//            User::where('agency_id', '!=', 0)->chunk(1000, function ($users) {
//                // Loop through users and store last monthly_diamond_received value in history
//                foreach ($users as $user) {
//                    $user->storeLastMonthlyDiamondReceivedInHistory();
//                }
//            });
//        } catch (\Exception $e) {
//        }





        //$this->info('update-room-user-now:cron Command Run Successfully !');
    }
}
