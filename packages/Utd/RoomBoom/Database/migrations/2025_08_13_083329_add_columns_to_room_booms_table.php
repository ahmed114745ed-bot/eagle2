<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToRoomBoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('room_booms', function (Blueprint $table) {
            $table->foreignId('trigger_gift_id')->nullable()->after('started_at');
            $table->foreignId('final_gift_id')->nullable()->after('ended_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('room_booms', function (Blueprint $table) {});
    }
}
