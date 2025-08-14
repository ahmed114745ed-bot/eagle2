<?php

namespace App\Console\Commands;

use App\Models\RealtimeProject;
use App\Models\User;
use App\Traits\Salaries\UserSalaryTrait;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

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
        $url = config('app.utd_url');
        $encryptKey = config('app.encrypt_key');
         $baseUrl =  config('app.url');

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
                $nowRealTime =   RealtimeProject::create([
                    'type' => $type,
                    'month' => $month,
                    'year' => $year,
                    'balance' => @$previous ? ((@$previous->balance ?? 0) - (@$previous->used ?? 0)) : 0,
                ]);
                $payload =  [
                    'balance' => $nowRealTime->balance,
                    'sub_balance' => @$previous->balance ?? 0,
                    'sub_used' => @$previous->used ?? 0,
                    'type'     => $type,
                    'base_url' => $baseUrl,
                ];
                $updateUrl = $url . 'realtime-projects/sub-month-update';
                $encryptedPayload = $this->encryptArray($payload, $encryptKey);
                $response =   Http::post($updateUrl,  [
                    'payload' => $encryptedPayload
                ]);
            }
        }
    }

    public static function encryptArray(array $data, string $key): string
    {
        $iv = substr($key, 0, 16); // 16 bytes for AES-256-CBC
        return openssl_encrypt(json_encode($data), 'AES-256-CBC', $key, 0, $iv);
    }
}
