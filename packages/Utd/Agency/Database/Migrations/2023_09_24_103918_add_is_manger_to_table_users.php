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
        if (! Schema::hasColumn('users', 'is_manger')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_manger')->default(false);
            });
        }

        if (! Schema::hasColumn('users', 'dashboard_manager_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->bigInteger('dashboard_manager_id')->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_manager');
            $table->dropColumn('dashboard_manager_id');
        });
    }
};
