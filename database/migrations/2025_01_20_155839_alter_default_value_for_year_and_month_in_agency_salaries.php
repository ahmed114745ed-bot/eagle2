<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('agency_sallaries', function (Blueprint $table) {
            $table->unsignedBigInteger('year')->default(0)->change();
            $table->unsignedBigInteger('month')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agency_sallaries', function (Blueprint $table) {
            $table->unsignedBigInteger('year')->default(null)->change();
            $table->unsignedBigInteger('month')->default(null)->change();
        });
    }
};
