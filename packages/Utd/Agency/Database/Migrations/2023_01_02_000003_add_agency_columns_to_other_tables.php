<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * إضافة أعمدة الوكالات في الجداول الأخرى
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. إضافة أعمدة الوكالات في جدول users
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'agency_id')) {
                    $table->unsignedInteger('agency_id')->nullable()->default(0);
                }
                if (!Schema::hasColumn('users', 'type_user')) {
                    $table->integer('type_user')->default(0)->comment('0:normal, 1:owner, 2:admin, 3:host, 4:professional');
                }
                if (!Schema::hasColumn('users', 'is_manger')) {
                    $table->boolean('is_manger')->default(false);
                }
                if (!Schema::hasColumn('users', 'is_host')) {
                    $table->unsignedTinyInteger('is_host')->nullable()->default(0);
                }
            });
        }

        // 2. إضافة agency_id في جدول gift_logs
        if (Schema::hasTable('gift_logs')) {
            Schema::table('gift_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('gift_logs', 'agency_id')) {
                    $table->unsignedInteger('agency_id')->nullable();
                }
            });
        }

        // 3. إضافة agency_id في جدول charges
        if (Schema::hasTable('charges')) {
            Schema::table('charges', function (Blueprint $table) {
                if (!Schema::hasColumn('charges', 'agency_id')) {
                    $table->integer('agency_id')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
