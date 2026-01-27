<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('user_achievement_levels', 'picked')) {
            Schema::table('user_achievement_levels', function (Blueprint $table) {
                $table->boolean('picked')->nullable()->default(false);
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
        Schema::table('user_achievement_levels', function (Blueprint $table) {
            $table->dropColumn('picked');
        });
    }
};
