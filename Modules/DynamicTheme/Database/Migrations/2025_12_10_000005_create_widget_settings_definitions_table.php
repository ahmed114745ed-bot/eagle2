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
        Schema::create('widget_settings_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('widget_id')->constrained('widgets')->onDelete('cascade');
            $table->string('setting_key')->comment('Key for the setting (e.g., height_percentage, columns)');
            $table->string('setting_label')->comment('Display label for the setting');
            $table->enum('setting_type', [
                'text',
                'number',
                'boolean',
                'select',
                'color',
                'json',
                'range'
            ])->default('text');
            $table->enum('setting_category', ['primary', 'secondary'])->default('primary')
                ->comment('primary = affects data, secondary = affects appearance');
            $table->text('default_value')->nullable()->comment('Default value for the setting');
            $table->json('options')->nullable()->comment('Options for select type: [{value, label}]');
            $table->json('validation_rules')->nullable()->comment('Validation rules: {min, max, required, etc.}');
            $table->boolean('is_hidden')->default(false)->comment('Hidden from client in dashboard');
            $table->integer('order')->default(0)->comment('Display order in dashboard');
            $table->timestamps();

            $table->index('widget_id');
            $table->unique(['widget_id', 'setting_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('widget_settings_definitions');
    }
};
