<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('box_uses', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('box_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('coins')->nullable();
            $table->integer('start_at')->nullable();
            $table->unsignedBigInteger('end_at')->nullable();
            $table->unsignedBigInteger('room_uid')->nullable();
            $table->unsignedBigInteger('room_id')->nullable();
            $table->integer('users_num')->default(1)->nullable();
            $table->unsignedTinyInteger('type')->comment('0=local 1=global')->nullable();
            $table->string('label')->nullable();
            $table->integer('used_num')->nullable();
            $table->integer('not_used_num')->nullable();
            $table->string('image')->nullable();
            $table->integer('used_coins')->nullable()->default(0);
            $table->integer('unused_coins')->nullable()->default(0);
            $table->boolean('is_closed')->default(true);
            $table->timestamps();

            $table->index(['room_id', 'end_at', 'not_used_num']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('box_uses');
    }
};
