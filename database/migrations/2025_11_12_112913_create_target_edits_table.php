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
        if (!Schema::hasTable('target_edits')) {
            Schema::create('target_edits', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('target_id');
                $table->unsignedBigInteger('edited_by')->nullable(); 
                $table->json('data'); 
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); 
                $table->timestamps();
            });
        }

        Schema::table('targets', function (Blueprint $table) {
            if (!Schema::hasColumn('targets', 'under_edit')) {
                $table->boolean('under_edit')->default(false);
            }
            if (!Schema::hasColumn('targets', 'edit_id')) {
                $table->unsignedBigInteger('edit_id')->nullable();
            }
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('targets', function (Blueprint $table) {
            $table->dropColumn(['under_edit', 'edit_id']);
        });
        Schema::dropIfExists('target_edits');
    }
};
