<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTargetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('targets')) {
            return;
        }
        Schema::create('targets', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('level')->nullable();
            $table->unsignedBigInteger('diamonds')->nullable();
            $table->unsignedBigInteger('minuts')->nullable();
            $table->unsignedBigInteger('hours')->nullable();
            $table->unsignedBigInteger('days')->nullable();
            $table->string('img')->nullable();
            $table->decimal('usd')->nullable();
            $table->decimal('coin')->nullable();
            $table->decimal('gold')->nullable();
            $table->boolean('under_edit')->default(false);
            $table->unsignedBigInteger('edit_id')->nullable();
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
        Schema::dropIfExists('targets');
    }
}
