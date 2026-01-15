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
            $table->foreignId('config_widget_override_id')
                ->constrained('config_widget_overrides')
                ->onDelete('cascade');
            $table->foreignId('theme_child_id')
                ->nullable()
                ->constrained('theme_children')
                ->onDelete('set null');
            
            // تكوين الشكل
            $table->json('shape_config')->nullable();
            
            // الألوان
            $table->json('color_config')->nullable();
            
            // الحدود والظلال
            $table->json('border_config')->nullable();
            $table->json('shadow_config')->nullable();
            
            // الخطوط والتباعد
            $table->json('typography_config')->nullable();
            $table->json('layout_config')->nullable();
            
            // المؤثرات
            $table->json('effects_config')->nullable();
            $table->json('animation_config')->nullable();
            
            // معلومات إضافية
            $table->string('child_key')->nullable();
            $table->string('child_name')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->integer('order')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['config_widget_override_id', 'theme_child_id']);
            $table->index('is_visible');
        });

        Schema::create('child_design_presets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('configuration_id')
                ->constrained('client_configurations')
                ->onDelete('cascade');
            
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('child_design')->comment('تصميم الأطفال');
            $table->boolean('is_default')->default(false);
            
            $table->timestamps();
            
            $table->index(['configuration_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('child_design_presets');
        Schema::dropIfExists('child_customizers');
    }
};
