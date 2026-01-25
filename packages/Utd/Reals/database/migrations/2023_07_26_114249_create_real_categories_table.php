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
        if (!Schema::hasTable('real_categories')) {
            Schema::create('real_categories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('real_id')->constrained('reals')->onDelete('cascade');
                $table->foreignId('category_id')->constrained('interests')->onDelete('cascade');
                $table->timestamps();
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
