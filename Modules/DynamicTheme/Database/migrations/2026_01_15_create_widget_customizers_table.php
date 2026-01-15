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
        Schema::create('widget_customizers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('config_widget_override_id')
                ->constrained('config_widget_overrides')
                ->onDelete('cascade');
            
            // شكل الويدجت
            $table->json('shape_config')->nullable()->comment('Border radius, shadow, custom shape');
            
            // نظام الألوان
            $table->json('color_config')->nullable()->comment('Primary, secondary, accent colors');
            $table->enum('color_format', ['hex', 'rgb', 'hsl'])->default('hex');
            
            // Gradient والخلفيات
            $table->json('gradient_config')->nullable()->comment('Gradient colors and direction');
            $table->json('background_config')->nullable()->comment('Background image, pattern, solid');
            
            // الحدود والظلال
            $table->json('border_config')->nullable()->comment('Border width, color, style, radius');
            $table->json('shadow_config')->nullable()->comment('Box shadow, text shadow');
            
            // الحركات والتأثيرات
            $table->json('animation_config')->nullable()->comment('Animation name, duration, timing');
            $table->json('transition_config')->nullable()->comment('Transition effects');
            
            // الخطوط والنصوص
            $table->json('typography_config')->nullable()->comment('Font family, size, weight, color');
            
            // التخطيط (Layout)
            $table->json('layout_config')->nullable()->comment('Padding, margin, alignment, spacing');
            
            // الشفافية والفلاتر
            $table->json('effects_config')->nullable()->comment('Opacity, blur, brightness filters');
            
            // معلومات إضافية
            $table->string('name')->nullable()->comment('اسم التخصيص');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            // الفهارس
            $table->index('config_widget_override_id');
            $table->index('is_active');
            $table->index('order');
        });

        // جدول لتخزين الألوان المحفوظة (Presets)
        Schema::create('color_presets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('configuration_id')
                ->constrained('client_configurations')
                ->onDelete('cascade');
            
            $table->string('name');
            $table->string('description')->nullable();
            $table->json('colors')->comment('Array of hex colors');
            $table->boolean('is_default')->default(false);
            
            $table->timestamps();
            
            $table->index('configuration_id');
            $table->index('is_default');
        });

        // جدول لتخزين الأشكال والتصاميم المحفوظة
        Schema::create('widget_design_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('configuration_id')
                ->constrained('client_configurations')
                ->onDelete('cascade');
            
            $table->string('name');
            $table->string('description')->nullable();
            $table->json('design_config')->comment('كل الإعدادات البصرية');
            $table->json('preview_data')->nullable();
            $table->boolean('is_public')->default(false);
            
            $table->timestamps();
            
            $table->index('configuration_id');
            $table->index('is_public');
        });

        // جدول لتخزين تخصيصات الأطفال (Children Customizers)
        Schema::create('child_customizers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('config_widget_override_id')
                ->constrained('config_widget_overrides')
                ->onDelete('cascade');
            $table->foreignId('theme_child_id')
                ->nullable()
                ->constrained('theme_children')
                ->onDelete('set null');
            
            // شكل الطفل
            $table->json('shape_config')->nullable()->comment('Border radius, shadow, custom shape');
            
            // نظام الألوان
            $table->json('color_config')->nullable()->comment('Background, text, border colors');
            
            // Gradient والخلفيات
            $table->json('gradient_config')->nullable()->comment('Gradient colors and direction');
            $table->json('background_config')->nullable()->comment('Background image, pattern, solid');
            
            // الحدود والظلال
            $table->json('border_config')->nullable()->comment('Border width, color, style, radius');
            $table->json('shadow_config')->nullable()->comment('Box shadow, text shadow');
            
            // الحركات والتأثيرات
            $table->json('animation_config')->nullable()->comment('Animation name, duration, timing');
            $table->json('transition_config')->nullable()->comment('Transition effects');
            
            // الخطوط والنصوص
            $table->json('typography_config')->nullable()->comment('Font family, size, weight, color');
            
            // التخطيط (Layout)
            $table->json('layout_config')->nullable()->comment('Padding, margin, alignment, spacing');
            
            // المواضع (Position)
            $table->json('position_config')->nullable()->comment('Top, left, width, height, z-index');
            
            // الشفافية والفلاتر
            $table->json('effects_config')->nullable()->comment('Opacity, blur, brightness filters');
            
            // معلومات إضافية
            $table->string('name')->nullable()->comment('اسم التخصيص');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            // الفهارس
            $table->index('config_widget_override_id');
            $table->index('theme_child_id');
            $table->index('is_active');
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('widget_design_templates');
        Schema::dropIfExists('color_presets');
        Schema::dropIfExists('widget_customizers');
    }
};
