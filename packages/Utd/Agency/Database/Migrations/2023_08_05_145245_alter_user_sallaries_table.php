<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUserSallariesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('user_sallaries')) return;
        if (!Schema::hasColumn('user_sallaries', 'owner_pide')) {
            Schema::table('user_sallaries', function (Blueprint $table) {
                $table->unsignedBigInteger('owner_pide')->nullable();
            });
        }
    }

    public function down()
    {
        Schema::table('user_sallaries', function (Blueprint $table) {
            // Revert the column change if needed
            $table->unsignedBigInteger('owner_pide')->nullable();
        });
    }
}
