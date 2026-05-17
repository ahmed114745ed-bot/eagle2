<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('area_polygons')) {
            return;
        }

        Schema::create('area_polygons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('area_manager_id');
            $table->json('coordinates')->nullable();
            $table->json('covered_countries')->nullable();
            $table->timestamps();

            $table->foreign('area_manager_id')
                ->references('id')
                ->on('admin_users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('area_polygons');
    }
};
