<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('child_customizers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('config_theme_child_override_id')
                ->nullable()
                ->constrained('config_theme_child_overrides')
                ->onDelete('cascade');
            $table->foreignId('theme_child_id')
                ->constrained('theme_children')
                ->onDelete('cascade');
            
            // تخصيص شكل الابن
            $table->json('shape_config')->nullable();
            $table->json('color_config')->nullable();
            $table->json('border_config')->nullable();
            $table->json('shadow_config')->nullable();
            $table->json('typography_config')->nullable();
            $table->json('layout_config')->nullable();
            $table->json('effects_config')->nullable();
            $table->json('animation_config')->nullable();
            
            // الرؤية والترتيب
            $table->boolean('is_visible')->default(true);
            $table->integer('display_order')->default(0);
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('config_theme_child_override_id');
            $table->index('theme_child_id');
            $table->index('is_visible');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('child_customizers');
    }
};
