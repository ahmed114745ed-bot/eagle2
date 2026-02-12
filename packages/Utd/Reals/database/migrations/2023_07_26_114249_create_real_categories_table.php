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
        if (! Schema::hasTable('real_categories')) {
            Schema::create('real_categories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('real_id');
                $table->unsignedBigInteger('category_id');
                $table->timestamps();

                // Only add foreign keys if referenced tables exist
                if (Schema::hasTable('reals')) {
                    $table->foreign('real_id')->references('id')->on('reals')->onDelete('cascade');
                }
                if (Schema::hasTable('interests')) {
                    $table->foreign('category_id')->references('id')->on('interests')->onDelete('cascade');
                }
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
        Schema::dropIfExists('real_categories');
    }
};
