<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('extra_data_in_rooms', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('user_id');
            $table->decimal('total', 10, 2);
            $table->unsignedInteger('room_id');
            $table->timestamps();
            
            // Define foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete ('cascade');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete ('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extra_data_in_rooms');
    }
};
