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
        Schema::create('boxs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedTinyInteger('type')->comment('0=local 1=global')->nullable();
            $table->unsignedBigInteger('coins')->nullable();
            $table->unsignedBigInteger('users')->comment('allowed users')->nullable();
            $table->string('image')->nullable();
            $table->boolean('has_label')->nullable();
            $table->string('default_label')->nullable();
            $table->integer('duration')->default('1')->comment('in minutes')->nullable();
            $table->string('dynamic_users_values')->nullable();
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
        Schema::dropIfExists('boxs');
    }
};
