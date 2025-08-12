<?php

namespace App\Console\Commands;

use App\Models\RealtimeProject;
use Illuminate\Support\Facades\Http;
use App\Traits\Salaries\UserSalaryTrait;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RealtimeProjectUtdCommand extends Command
{
    use UserSalaryTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'realtime-project-utd';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update realtime project utd every hour';

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
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;
        $url = config('utd_url');
        $projectId = config('project_id');
        $fullUrl = $url . $projectId . '/realtime-projects';
        $response =  Http::get($fullUrl);

        if ($response->successful()) {
            $data = $response->json();
            foreach ($data as $realtimeProject) {
                $realtime =     RealtimeProject::where(['month' => $month, 'year' => $year, 'type', $realtimeProject->type])->first();
                if (!$realtime) {
                    $realtime =   RealtimeProject::create(['month' => $month, 'year' => $year, 'type', $realtimeProject->type]);
                }
                $realtime->balance += $realtimeProject->balance;
                $realtime->save();
                $data = [
                    'type' => $realtime->type,
                    'used' => $realtime->used,
                ];
                Http::post($fullUrl, $data);
            }
        }
    }
}
