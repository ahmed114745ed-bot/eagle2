<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCpInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cp_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_one_id')->constrained('users', 'id')->onDelete('cascade');
            $table->foreignId('user_two_id')->constrained('users', 'id')->onDelete('cascade');
            $table->integer('status')->default(0)->comment("0=>cancel,1=>acceptation");
            $table->integer("exp")->default(0);
            $table->integer("sub_exp")->default(0);
            $table->integer("level")->default(0);
            $table->integer("sub_level")->default(0);
            $table->integer("last_active_date")->nullable();
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
        Schema::dropIfExists('cp_infos');
    }
}
