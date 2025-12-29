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
        Schema::create('theme_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('theme_id')->constrained('widget_themes')->onDelete('cascade');
            $table->string('asset_key')->comment('Key for the asset (e.g., frame_rank_1, background)');
            $table->string('asset_label')->comment('Display label for the asset');
            $table->enum('asset_type', ['image', 'svga', 'vap', 'alpha'])->default('image');
            $table->string('default_url')->nullable()->comment('Default asset URL');
            $table->boolean('is_required')->default(false)->comment('Is this asset required?');
            $table->integer('order')->default(0)->comment('Display order in dashboard');
            $table->timestamps();

            $table->index('theme_id');
            $table->unique(['theme_id', 'asset_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('theme_assets');
    }
};
