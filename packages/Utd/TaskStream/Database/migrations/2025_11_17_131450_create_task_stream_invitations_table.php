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
        Schema::create('task_stream_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_stream_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('inviter_user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('invitee_user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_stream_invitations');
    }
};
