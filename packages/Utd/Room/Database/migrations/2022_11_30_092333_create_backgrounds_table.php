<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBackgroundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('backgrounds', function (Blueprint $table) {
            $table->increments('id');
            $table->string('img');
            $table->unsignedTinyInteger('enable')->default('1')->comment('Whether to enable 1 enable 2 disable')->nullable();
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
        Schema::dropIfExists('backgrounds');
    }
}
