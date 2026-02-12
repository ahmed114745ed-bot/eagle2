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
        Schema::create('pk_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_stream_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('team_1')->default('0,0');
            $table->string('team_2')->default('0,0');
            $table->boolean('status')->default(0);
            $table->tinyInteger('winner')->nullable();
            $table->double('team_1_score')->nullable();
            $table->double('team_2_score')->nullable();
            $table->dateTime('ends_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pk_sessions');
    }
};
