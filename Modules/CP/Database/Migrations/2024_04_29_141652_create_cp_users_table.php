<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCpUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cp_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_one_id')->constrained('users', 'id')->onDelete('cascade');
            $table->foreignId('user_two_id')->constrained('users', 'id')->onDelete('cascade');
            $table->integer('status')->default(0)->comment("0=>pending,1=>accept,2=>denid,3=>cancel cp");
            $table->string("last_active_date")->nullable();
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
        Schema::dropIfExists('cp_users');
    }
}
