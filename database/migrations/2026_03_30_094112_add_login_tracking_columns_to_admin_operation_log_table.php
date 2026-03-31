<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('admin_operation_log', function (Blueprint $table) {
            $table->string('country')->nullable()->after('ip');
            $table->string('city')->nullable()->after('country');
            $table->string('region')->nullable()->after('city');
            $table->string('latitude')->nullable()->after('region');
            $table->string('longitude')->nullable()->after('latitude');
            $table->string('device')->nullable()->after('longitude');
            $table->string('platform')->nullable()->after('device');
            $table->string('platform_version')->nullable()->after('platform');
            $table->string('browser')->nullable()->after('platform_version');
            $table->string('browser_version')->nullable()->after('browser');
            $table->text('user_agent')->nullable()->after('browser_version');
            $table->timestamp('login_at')->nullable()->after('user_agent');
            $table->timestamp('logout_at')->nullable()->after('login_at');
            $table->unsignedInteger('session_duration_minutes')->nullable()->after('logout_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_operation_log', function (Blueprint $table) {
            $table->dropColumn([
                'country',
                'city',
                'region',
                'latitude',
                'longitude',
                'device',
                'platform',
                'platform_version',
                'browser',
                'browser_version',
                'user_agent',
                'login_at',
                'logout_at',
                'session_duration_minutes',
            ]);
        });
    }
};
