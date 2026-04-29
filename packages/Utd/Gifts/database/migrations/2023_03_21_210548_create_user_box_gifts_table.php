<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserBoxGiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('user_box_gifts')) {
            Schema::create('user_box_gifts', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->integer('coins')->nullable();
                $table->unsignedTinyInteger('type')->comment('0=local 1=global')->nullable();
                $table->unsignedBigInteger('box_uses_owner_id')->nullable();
                $table->string('image')->nullable();
                $table->string('label')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('box_uses') && Schema::hasTable('user_box_gifts') && ! Schema::hasColumn('user_box_gifts', 'box_uses_id')) {
            Schema::table('user_box_gifts', function (Blueprint $table) {
                $table->unsignedBigInteger('box_uses_id')->nullable();
            });
        }

        if (Schema::hasTable('rooms') && Schema::hasTable('user_box_gifts') && ! Schema::hasColumn('user_box_gifts', 'room_id')) {
            Schema::table('user_box_gifts', function (Blueprint $table) {
                $table->unsignedBigInteger('room_uid')->nullable();
                $table->unsignedBigInteger('room_id')->nullable();
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
        Schema::dropIfExists('user_box_gifts');
    }
}
