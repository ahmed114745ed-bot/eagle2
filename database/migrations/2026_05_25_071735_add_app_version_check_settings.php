<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $settings = [
            [
                'key' => 'android_current_version',
                'value' => '1.0.0',
                'type' => 'text',
                'input_type' => 'text',
            ],
            [
                'key' => 'android_min_version',
                'value' => '1.0.0',
                'type' => 'text',
                'input_type' => 'text',
            ],
            [
                'key' => 'android_update_required',
                'value' => '0',
                'type' => 'boolean',
                'input_type' => 'checkbox',
            ],
            [
                'key' => 'ios_current_version',
                'value' => '1.0.0',
                'type' => 'text',
                'input_type' => 'text',
            ],
            [
                'key' => 'ios_min_version',
                'value' => '1.0.0',
                'type' => 'text',
                'input_type' => 'text',
            ],
            [
                'key' => 'ios_update_required',
                'value' => '0',
                'type' => 'boolean',
                'input_type' => 'checkbox',
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $keys = [
            'android_current_version',
            'android_min_version',
            'android_update_required',
            'ios_current_version',
            'ios_min_version',
            'ios_update_required',
        ];

        DB::table('settings')->whereIn('key', $keys)->delete();
    }
};
