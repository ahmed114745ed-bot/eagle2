<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgencySallariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('agency_sallaries')) {
            return;
        }
        Schema::create('agency_sallaries', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('agency_id')->default(0);
            $table->decimal('sallary', 20, 4)->default(0);
            $table->decimal('cut_amount', 20, 4)->default(0);
            $table->integer('month')->default(0);
            $table->integer('year')->default(0);
            $table->boolean('is_paid')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('agency_sallaries');
    }
}
