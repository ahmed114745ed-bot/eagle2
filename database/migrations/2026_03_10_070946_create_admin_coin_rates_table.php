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
        Schema::create('admin_coin_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('admin_id');
            $table->decimal('rate', 20, 2)->default(0);
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('admin_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_coin_rates');
    }
};
