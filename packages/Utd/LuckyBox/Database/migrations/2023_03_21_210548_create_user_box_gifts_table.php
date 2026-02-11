<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_box_gifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('box_uses_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('coins')->nullable();
            $table->unsignedBigInteger('room_uid')->nullable();
            $table->unsignedBigInteger('box_uses_owner_id')->nullable();
            $table->unsignedBigInteger('room_id')->nullable();
            $table->unsignedTinyInteger('type')->comment('0=local 1=global')->nullable();
            $table->string('image')->nullable();
            $table->string('label')->nullable();
            $table->timestamps();

            $table->index(['box_uses_id', 'user_id']);
        });
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
};
