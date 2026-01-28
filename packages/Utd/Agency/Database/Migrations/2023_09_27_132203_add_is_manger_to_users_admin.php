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
        if (!Schema::hasTable('admin_users')) return;
        Schema::table('admin_users', function (Blueprint $table) {
            if (!Schema::hasColumn('admin_users', 'Agency_manger')) {
                $table->boolean('Agency_manger')->default(false);
            }
            if (!Schema::hasColumn('admin_users', 'app_id')) {
                $table->bigInteger('app_id')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_users', function (Blueprint $table) {
            $table->dropColumn('Agency_manger');
            $table->dropColumn('app_id');
        });
    }
};
