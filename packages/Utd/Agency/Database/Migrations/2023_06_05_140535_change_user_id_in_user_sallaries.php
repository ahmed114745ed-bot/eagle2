<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeUserIdInUserSallaries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('user_sallaries')) return;
        Schema::table('user_sallaries', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->default (0)->change ();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_sallaries', function (Blueprint $table) {
            //
        });
    }
}
