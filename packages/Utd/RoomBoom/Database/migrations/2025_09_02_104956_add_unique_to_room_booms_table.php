<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueToRoomBoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('room_booms', function (Blueprint $table) {
            $table->unique(['total_room_gift_id', 'room_boom_level_id'], 'unique_roomboom_level');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('room_booms', function (Blueprint $table) {
            $table->dropForeign(['total_room_gift_id']);
            $table->dropForeign(['room_boom_level_id']);
            $table->dropUnique('unique_roomboom_level');
            $table->foreign('total_room_gift_id')->references('id')->on('total_room_gifts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('room_boom_level_id')->references('id')->on('room_boom_levels')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }
}
