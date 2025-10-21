<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('area_polygons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_manager_id')->constrained('admin_users')->onDelete('cascade');
            $table->json('coordinates');
            $table->json('covered_countries')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('area_polygons');
    }
};
