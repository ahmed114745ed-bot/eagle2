<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSalaryToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasColumn('users', 'salary')) {
            Schema::table('users', function (Blueprint $table) {
                $table->float('salary', 20, 2)->default(0);
            });
        }
        if (Schema::hasTable('agencies') && ! Schema::hasColumn('agencies', 'salary')) {
            Schema::table('agencies', function (Blueprint $table) {
                $table->float('salary', 20, 2)->default(0);
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
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}
