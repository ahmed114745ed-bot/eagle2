<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVipToUsersExpireToOvip extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('users', 'vip')) {
            Schema::table('users', function (Blueprint $table) {
                $table->integer('vip')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('o_vips', 'expire')) {
            Schema::table('o_vips', function (Blueprint $table) {
                $table->integer('expire')->nullable()->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('users', 'vip')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('vip');
            });
        }
        if (Schema::hasColumn('o_vips', 'expire')) {
            Schema::table('o_vips', function (Blueprint $table) {
                $table->dropColumn('expire');
            });
        }
    }
}
