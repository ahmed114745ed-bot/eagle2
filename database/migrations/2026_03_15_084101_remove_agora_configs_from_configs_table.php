<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {

         $legacyKeys = [
            'agora_rtc_app_id',
            'agora_rtc_app_certificate',
            'agora_rtm_app_id',
            'agora_rtm_app_certificate',
            'tencent_app_id',
            'tencent_server_secret',
        ];

        DB::table('configs')->whereIn('name', $legacyKeys)->delete();

        $utdConfigs = [
            ['key' => 'utd_app_id', 'value' => '', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'utd_api_key', 'value' => '', 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($utdConfigs as $config) {
            DB::table('configs')->updateOrInsert(
                ['name' => $config['key']],
                ['value' => $config['value'], 'updated_at' => $config['updated_at'], 'created_at' => $config['created_at']]
            );
        }
    }

    public function down()
    {
        DB::table('configs')->insert([
            [
                'name' => 'agora_rtc_app_id',
                'value' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'agora_rtc_app_certificate',
                'value' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'agora_rtm_app_id',
                'value' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'agora_rtm_app_certificate',
                'value' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
};