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
        $url = config('app.utd_url');
        $fullUrl = $url . 'realtime-projects';
        $baseUrl =  config('app.url');
        $encryptKey = config('app.encrypt_key');
        $dataRequest = [
            'base_url' => $baseUrl,
        ];
        $encryptedPayload = urlencode($this->encryptArray($dataRequest, $encryptKey));
        $response =  Http::get($fullUrl, [
            'payload' => $encryptedPayload
        ]);

        if ($response->successful()) {
            $data = $response->json();

            if (!empty($data['data'])) {
                foreach ($data['data'] as $realtimeProject) {
                    // Fix where syntax
                    $realtime = RealtimeProject::where([
                        'month' => $month,
                        'year' => $year,
                        'type'  => $realtimeProject['type'], // or ->type if it's an object
                    ])->first();

                    if ($realtime) {
                        // $realtime = RealtimeProject::create([
                        //     'month' => $month,
                        //     'year'  => $year,
                        //     'type'  => $realtimeProject['type'],
                        // ]);

                        $realtime->balance = $realtimeProject['balance'];
                        $realtime->save();

                        // Merge array values instead of +=
                        $payload = array_merge($dataRequest, [
                            'type' => $realtime->type,
                            'used' => $realtime->used,
                        ]);
                        $updateUrl = $url . 'realtime-projects/update';
                        $encryptedPayload = $this->encryptArray($payload, $encryptKey);
                        $response =   Http::post($updateUrl,  [
                            'payload' => $encryptedPayload
                        ]);
                    }
                }
            }
        }
    }

    public static function encryptArray(array $data, string $key): string
    {
        $iv = substr($key, 0, 16); // 16 bytes for AES-256-CBC
        return openssl_encrypt(json_encode($data), 'AES-256-CBC', $key, 0, $iv);
    }
}
