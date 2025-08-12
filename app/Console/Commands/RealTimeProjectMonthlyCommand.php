<?php

namespace App\Console\Commands;

use App\Models\RealtimeProject;
use App\Models\User;
use App\Traits\Salaries\UserSalaryTrait;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RealTimeProjectMonthlyCommand extends Command
{
    use UserSalaryTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'realtime-project';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update realtime project after 30 days';

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
        $month = now()->month;
        $year = now()->year;

        // Get projects for current month and year
        $realtimeProjects = RealtimeProject::where('month', $month)
            ->where('year', $year)
            ->get();

        if ($realtimeProjects->isEmpty()) {

            $previousMonth = now()->subMonth();
            $previousMonthProjects = RealtimeProject::where('month', $previousMonth->month)
                ->where('year', $year)
                ->get();

            foreach (['audio', 'video'] as $type) {
                $previous = $previousMonthProjects->where('type', $type)->first();

                RealtimeProject::create([
                    'type' => $type,
                    'month' => $month,
                    'year' => $year,
                    'balance' => $previous ? ($previous->balance - $previous->used) : null,
                ]);
            }
        }
    }
}
