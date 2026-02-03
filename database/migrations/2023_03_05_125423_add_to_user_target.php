<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddToUserTarget extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_target', function (Blueprint $table) {
            if (!Schema::hasColumn('user_target', 'agency_obtain')) {
                $table->double('agency_obtain')->nullable()->default(0);
            }
            if (!Schema::hasColumn('user_target', 'user_obtain')) {
                $table->double('user_obtain')->nullable()->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_target', function (Blueprint $table) {
            //
        });
    }
}
