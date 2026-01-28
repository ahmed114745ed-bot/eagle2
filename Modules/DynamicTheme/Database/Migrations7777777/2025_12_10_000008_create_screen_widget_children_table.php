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
        Schema::create('screen_widget_children', function (Blueprint $table) {
            $table->id();
            $table->foreignId('screen_widget_id')->constrained('screen_widgets')->onDelete('cascade');
            $table->string('child_key')->comment('Unique key for the child (e.g., hot, egypt, trending)');
            $table->string('child_type')->nullable()->comment('Type of child: tab, category, special (search, join_room)');
            $table->string('label')->comment('Display label');
            $table->integer('order')->default(0)->comment('Display order');
            $table->boolean('is_visible')->default(true)->comment('Is visible to users?');
            $table->boolean('is_active')->default(false)->comment('Is currently active/selected?');
            $table->json('action')->nullable()->comment('Action: {type, screen_key, filter_key, filter_value, action_key}');
            $table->json('assets')->nullable()->comment('Assets: {icon: {type, url}, background: {type, url}}');
            $table->string('position')->nullable()->comment('Position for special children: left, right');
            $table->timestamps();

            $table->index('screen_widget_id');
            $table->index('child_key');
            $table->index('order');
            $table->unique(['screen_widget_id', 'child_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('screen_widget_children');
    }
};
