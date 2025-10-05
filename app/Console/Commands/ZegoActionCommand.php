<?php

namespace App\Console\Commands;


use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Traits\Salaries\UserSalaryTrait;


class ZegoActionCommand extends Command
{
    use UserSalaryTrait;

    protected $signature = 'zego-action';

    protected $description = 'add user achievements after 30 day';


    public function __construct()
    {
        parent::__construct();
    }
    public function handle()
    {
        $url = config('app.utd_url');
       
        $fullUrl = $url . 'zego-action';
        $baseUrl =  config('app.url');

        $response = Http::get($fullUrl, [
            'base_url' => $baseUrl,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $isActive = $data['data'];

            $keys = 'zego_feature';

            $setting =   Setting::where('key', $keys)->first();
            if ($setting) {
                $setting->value = $isActive;
                $setting->save();
            } else {
                Setting::create([
                    'key' => $keys,
                    'value' => $isActive,
                ]);
            }
        }
    }
}
