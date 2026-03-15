<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::table('configs')->whereIn('name', [
            'agora_rtc_app_id',
            'agora_rtc_app_certificate',
            'agora_rtm_app_id',
            'agora_rtm_app_certificate',
        ])->delete();
    }

    public function down()
    {
        DB::table('configs')->insert([
            [
                'name' => 'agora_rtc_app_id',
                'value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'agora_rtc_app_certificate',
                'value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'agora_rtm_app_id',
                'value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'agora_rtm_app_certificate',
                'value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
};