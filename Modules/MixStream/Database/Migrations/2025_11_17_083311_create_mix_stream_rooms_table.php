<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMixStreamRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mix_stream_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mix_stream_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('room_id');

            $table->foreign('room_id')->references('id')->on('rooms')->cascadeOnDelete();
            $table->unique(['mix_stream_id', 'room_id'], 'ux_mix_room');
            $table->index('room_id', 'idx_mix_rooms_room');
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
        Schema::dropIfExists('mix_stream_rooms');
    }
}
