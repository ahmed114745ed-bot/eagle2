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
        Schema::create('widget_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('widget_id')->constrained('widgets')->onDelete('cascade');
            $table->enum('action_type', ['screen', 'webview', 'filter', 'internal'])
                ->comment('Type of action');
            $table->string('action_label')->comment('Display label for the action');
            $table->boolean('requires_target')->default(true)
                ->comment('Does this action require a target value?');
            $table->string('target_type')->nullable()
                ->comment('Type of target: screen_key, url, filter_key, action_key');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('widget_id');
            $table->unique(['widget_id', 'action_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('widget_actions');
    }
};
