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
        Schema::create('client_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('client_id')->index()->comment('Client/App identifier');
            $table->string('name')->comment('Configuration name (e.g., Christmas 2025, Ramadan)');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(false)->comment('Only one can be active per client');
            $table->timestamps();

            $table->unique(['client_id', 'name']);
            $table->index(['client_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_configurations');
    }
};
