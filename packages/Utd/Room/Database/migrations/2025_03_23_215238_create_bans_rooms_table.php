<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBansRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bans_rooms', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('room_id')->nullable();
            $table->unsignedBigInteger('duration')->default('10000');
            $table->unsignedBigInteger('staff_id')->nullable();
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
        Schema::dropIfExists('bans_rooms');
    }
}
